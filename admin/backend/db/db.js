const mysql = require('mysql2');

const connection = mysql.createConnection({
  host: '127.0.0.1',
  user: 'root',
  password: '',
  database: 'jo_project_starter'
});

connection.connect((err) => {
  if (err) {
    console.error('Échec de la connexion à la base de données :', err.message);
    return;
  }
  console.log('Connexion à la base de données réussie.');
});
