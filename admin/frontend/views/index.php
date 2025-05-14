<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des matchs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: white;
        }
        .actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .actions input,
        .actions select,
        .actions button {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .actions button {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .actions button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<h2>Administration des matchs</h2>

<table id="matches-table">
    <thead>
        <tr>
            <th>Date & Heure</th>
            <th>Stade</th>
            <th>Équipe Domicile</th>
            <th>Équipe Extérieure</th>
            <th>Score</th>
            <th>Gagnant</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="matches-body">
        <!-- Les matchs seront injectés ici par JS -->
    </tbody>
</table>

<script>
    const API_URL = "http://localhost:3000";

    // Récupérer tous les matchs
    async function fetchMatches() {
        const res = await fetch(`${API_URL}/admin/matches`);
        const matches = await res.json();
        displayMatches(matches);
    }

    // Afficher les matchs dans le tableau
    function displayMatches(matches) {
        const tbody = document.getElementById("matches-body");
        tbody.innerHTML = ""; // Nettoyer le tableau

        matches.forEach(match => {
            const tr = document.createElement("tr");

            const dateStr = new Date(match.start).toLocaleString("fr-FR", {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            tr.innerHTML = `
                <td><input type="datetime-local" value="${match.start.slice(0, 16)}" class="start-input" /></td>
                <td>${match.stadium} (${match.location})</td>
                <td>${match.home_team || "?"}</td>
                <td>${match.away_team || "?"}</td>
                <td><input type="text" class="score-input" value="${match.score || ""}" placeholder="Ex: 2-1" /></td>
                <td>
                    <select class="winner-select">
                        <option value="">Choisir le gagnant</option>
                        <option value="${match.home_team_id}">${match.home_team}</option>
                        <option value="${match.away_team_id}">${match.away_team}</option>
                    </select>
                </td>
                <td class="actions">
                    <button onclick="updateMatch(${match.id}, this)">Mettre à jour</button>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    // Mettre à jour un match via l’API
    async function updateMatch(matchId, button) {
        const row = button.closest("tr");
        const start = row.querySelector(".start-input").value;
        const score = row.querySelector(".score-input").value;
        const winner = row.querySelector(".winner-select").value;

        if (!start || !score || !winner) {
            alert("Tous les champs doivent être remplis.");
            return;
        }

        const response = await fetch(`${API_URL}/admin/match/${matchId}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ start, score, winner })
        });

        const result = await response.json();

        if (response.ok) {
            alert("Match mis à jour !");
            fetchMatches(); // Recharger les données
        } else {
            alert("Erreur : " + result.error);
        }
    }

    // Charger les matchs au démarrage
    fetchMatches();
</script>

</body>
</html>
