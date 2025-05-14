<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Administrateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input {
            display: block;
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            width: 100%;
            border-radius: 4px;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
        }
        
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <form method="POST" id="registrationForm">
        <label for="username">Nom d'utilisateur:</label>
        <input type="text" id="username" name="username" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">S'inscrire</button>
    </form>
    
    <a href="login.php">Déjà inscrit, je me connecte.</a>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Récupération des valeurs du formulaire
            const username = this.username.value.trim();
            const email = this.email.value.trim();
            const password = this.password.value;
            
            // Validation des champs
            if (!username || !email || !password) {
                alert("Tous les champs sont requis.");
                return;
            }
            
            try {
                // Envoi des données au serveur
                const response = await fetch('http://localhost:3000/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        email: email,
                        password: password,
                        is_superuser: true
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Gestion de la réponse positive
                    alert("Inscription réussie !");
                    localStorage.setItem('token', data.token);
                    window.location.href = 'index.html';
                } else {
                    // Gestion des erreurs du serveur
                    alert(data.message || 'Erreur lors de l\'inscription');
                }
            } catch (error) {
                // Gestion des erreurs réseau
                console.error('Erreur réseau :', error);
                alert('Erreur réseau ou serveur.');
            }
        });
    </script>
</body>
</html>