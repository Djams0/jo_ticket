const express = require('express');
const mysql = require('mysql2');
const jwt = require('jsonwebtoken');
const bodyParser = require('body-parser');
const cors = require('cors');
const QRCode = require('qrcode');

const app = express();
app.use(cors());
app.use(bodyParser.json());

const SECRET_KEY = 'SECRET_KEY'; // Clé secrète pour JWT

// Configurer la connexion MySQL
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'jo_project_starter',
});

db.connect(err => {
  if (err) throw err;
  console.log('✅ Connecté à MySQL');
});

// Middleware pour authentification avec JWT
const authenticateToken = (req, res, next) => {
    const token = req.headers['authorization']?.split(' ')[1];
    if (!token) return res.sendStatus(401);

    jwt.verify(token, SECRET_KEY, (err, user) => {
        if (err) return res.sendStatus(403);
        req.user = user;
        next();
    });
};

// Inscription utilisateur (Le hash du mot de passe est géré en PHP)
app.post('/register', (req, res) => {
  const { username, email, password, is_superuser } = req.body;

  if (!username || !email || !password || typeof is_superuser === 'undefined') return res.status(400).send('Tous les champs sont obligatoires');

  const query = "INSERT INTO auth_user (username, email, password, is_superuser) VALUES (?, ?, SHA2(?, 256), ?)";
  db.query(query, [username, email, password, is_superuser], (err, result) => {
    if (err) return res.status(500).send(err);

    const userId = result.insertId;

    // Création du token JWT
    const token = jwt.sign({ id: userId, username, is_superuser }, SECRET_KEY, { expiresIn: '48h' });

    res.status(201).json({ token, user: { id: userId, username, email, is_superuser} });
  });
});


// Connexion utilisateur
app.post('/login', (req, res) => {
  const { email, password } = req.body;

  if (!email || !password) return res.status(400).send('Email et mot de passe requis');

  const query = "SELECT id, username, email, password, is_superuser FROM auth_user WHERE email = ? AND password = SHA2(?, 256)";
  db.query(query, [email, password], (err, results) => {
    if (err) return res.status(500).send(err);
    
    if (results.length === 0) return res.status(401).send('Identifiants incorrects');

    const user = results[0];
    const token = jwt.sign({ id: user.id, username: user.username, is_superuser: user.is_superuser }, SECRET_KEY, { expiresIn: '48h' });

    res.json({ token, user });
  });
});

// Liste des événements (Matchs)
app.get('/events', (req, res) => {
  const query = `
    SELECT e.id, e.start, s.name AS stadium, 
           t1.name AS team_home, t2.name AS team_away, e.score, e.winner_id 
    FROM mainapp_event e
    LEFT JOIN mainapp_stadium s ON e.stadium_id = s.id
    LEFT JOIN mainapp_team t1 ON e.team_home_id = t1.id
    LEFT JOIN mainapp_team t2 ON e.team_away_id = t2.id
    ORDER BY e.start ASC
  `;
  db.query(query, (err, results) => {
    if (err) return res.status(500).send(err);
    res.json(results);
  });
});

// Acheter un billet (JWT obligatoire)
app.post('/tickets', authenticateToken, (req, res) => {
  const { event_id, category, price } = req.body;
  const user_id = req.user.id; 

  if (!event_id || !category || !price) return res.status(400).send('Données manquantes');

  // Créez un identifiant unique pour chaque ticket
  const ticketIdentifier = `event:${event_id}-user:${user_id}-category:${category}-price:${price}-${Date.now()}`;

  // Générez un QR code en fonction de cet identifiant unique
  QRCode.toDataURL(ticketIdentifier, (err, qrCodeUrl) => {
    if (err) return res.status(500).send('Erreur de génération du QR Code');

    // Insertion dans la base de données avec le QR code généré
    const query = "INSERT INTO mainapp_ticket (event_id, user_id, category, price, qr_code) VALUES (?, ?, ?, ?, ?)";
    db.query(query, [event_id, user_id, category, price, qrCodeUrl], (err, result) => {
      if (err) return res.status(500).send(err);
      res.status(201).send({ message: 'Billet acheté avec succès', qrCodeUrl });
    });
  });
});

// Vérifier un billet (JWT obligatoire)
app.post('/scan-ticket', authenticateToken, (req, res) => {
  const { ticket_id } = req.body;
  const scanner_id = req.user.id;

  if (!ticket_id) return res.status(400).send('ID du billet requis');

  // Vérifier si le billet est déjà utilisé
  db.query("SELECT used FROM mainapp_ticket WHERE id = ?", [ticket_id], (err, results) => {
    if (err) return res.status(500).send(err);
    if (results.length === 0) return res.status(404).send('Billet non trouvé');
    
    if (results[0].used) return res.status(400).send('Billet déjà utilisé');

    // Mettre à jour l'état du billet
    db.query("UPDATE mainapp_ticket SET used = 1 WHERE id = ?", [ticket_id], err => {
      if (err) return res.status(500).send(err);

      // Enregistrer le scan
      db.query("INSERT INTO mainapp_ticket_scan (ticket_id, scanner_id) VALUES (?, ?)", [ticket_id, scanner_id], err => {
        if (err) return res.status(500).send(err);
        res.send('Billet validé avec succès');
      });
    });
  });
});

// Liste des billets d'un utilisateur (JWT obligatoire)
app.get('/my-tickets', authenticateToken, (req, res) => {
  const user_id = req.user.id;

  const query = `
    SELECT 
      t.id AS ticket_id, 
      t.qr_code, 
      t.used, 
      t.category, 
      e.start, 
      s.name AS stadium, 
      s.location AS stadium_location, 
      th.name AS home_team, 
      ta.name AS away_team
    FROM mainapp_ticket t
    JOIN mainapp_event e ON t.event_id = e.id
    JOIN mainapp_stadium s ON e.stadium_id = s.id
    LEFT JOIN mainapp_team th ON e.team_home_id = th.id
    LEFT JOIN mainapp_team ta ON e.team_away_id = ta.id
    WHERE t.user_id = ?
    ORDER BY e.start ASC
  `;

  db.query(query, [user_id], (err, results) => {
    if (err) {
      console.error(err);
      return res.status(500).json({ message: 'Database query error' });
    }

    // Retourner les résultats sous forme de JSON
    res.json(results);
  });
});


// ✅ Route admin : afficher tous les matchs
app.get('/admin/matches', async (req, res) => {
  try {
    const [matches] = await db.promise().execute(`
      SELECT 
        e.id, e.start, e.score, 
        s.name AS stadium, s.location,
        th.name AS home_team, ta.name AS away_team,
        e.team_home_id, e.team_away_id,
        w.name AS winner
      FROM mainapp_event e
      LEFT JOIN mainapp_stadium s ON e.stadium_id = s.id
      LEFT JOIN mainapp_team th ON e.team_home_id = th.id
      LEFT JOIN mainapp_team ta ON e.team_away_id = ta.id
      LEFT JOIN mainapp_team w ON e.winner_id = w.id
      ORDER BY e.start
    `);

    res.json(matches);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Erreur lors de la récupération des matchs.' });
  }
});

// ✅ Route admin : mettre à jour un match
app.put('/admin/match/:id', async (req, res) => {
  const matchId = req.params.id;
  const { start, score, winner } = req.body;

  if (!start || !score || !winner) {
    return res.status(400).json({ error: 'Champs requis manquants.' });
  }

  try {
    const [result] = await db.promise().execute(`
      UPDATE mainapp_event
      SET start = ?, score = ?, winner_id = ?
      WHERE id = ?
    `, [start, score, winner, matchId]);

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: 'Match non trouvé.' });
    }

    res.json({ message: 'Match mis à jour avec succès.' });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Erreur lors de la mise à jour du match.' });
  }
});




// Lancer le serveur
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`🚀 Serveur Node.js démarré sur http://localhost:${PORT}`);
});
