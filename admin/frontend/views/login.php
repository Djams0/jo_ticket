<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        form {
            max-width: 400px;
            margin: 2em auto;
            padding: 2em;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        a {
            display: block;
            text-align: center;
            margin-top: 1.5em;
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }
        
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="POST" id="loginForm">
        <h2 style="text-align: center; margin-bottom: 1.5em; color: #333;">Connexion Administrateur</h2>
        
        <label for="username_or_email">Nom d'utilisateur ou E-mail:</label>
        <input type="text" id="username_or_email" name="username_or_email" required>
        
        <label for="password">Mot de passe:</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Se connecter</button>
    </form>
    
    <a href="register.php">Pas encore inscrit ? Je m'inscris</a>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Récupération des valeurs du formulaire
            const identifier = this.username_or_email.value.trim();
            const password = this.password.value;
            
            // Validation des champs
            if (!identifier || !password) {
                alert("Tous les champs sont requis.");
                return;
            }
            
            try {
                // Envoi des données au serveur
                const response = await fetch('http://localhost:3000/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: identifier, // À adapter si vous utilisez le nom d'utilisateur
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Vérification des droits administrateur
                    if (data.user && data.user.is_superuser) {
                        alert("Connexion réussie !");
                        localStorage.setItem('token', data.token);
                        window.location.href = "index.html";
                    } else {
                        alert("Accès refusé : vous n'êtes pas administrateur.");
                    }
                } else {
                    alert(data.message || "Échec de la connexion.");
                }
            } catch (error) {
                console.error("Erreur de connexion :", error);
                alert("Erreur réseau ou serveur.");
            }
        });
    </script>
</body>
</html>