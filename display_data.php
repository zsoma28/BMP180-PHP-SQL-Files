<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMP180 – Élő Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        
        .status-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.9em; }
        .status-ok { color: #27ae60; font-weight: bold; }
        .status-error { color: #e74c3c; font-weight: bold; }

        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #4CAF50; color: white; text-transform: uppercase; letter-spacing: 1px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; transition: 0.3s; }

        .temp-val { font-weight: bold; color: #d35400; }
        .press-val { font-weight: bold; color: #2980b9; }
        
        /* Animáció az új soroknak */
        @keyframes highlight {
            0% { background-color: #d4edda; }
            100% { background-color: transparent; }
        }
        .new-row { animation: highlight 2s ease-out; }
    </style>
</head>
<body>

<div class="container">
    <h2>BMP180 Szenzor Élő Adatok</h2>
    
    <div class="status-bar">
        <div>Rendszerállapot: <span id="connection-status" class="status-ok">Kapcsolódva</span></div>
        <div>Utolsó frissítés: <span id="last-update">-</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hőmérséklet (°C)</th>
                <th>Légnyomás (hPa)</th>
                <th>Időpont</th>
            </tr>
        </thead>
        <tbody id="data-table-body">
            <tr><td colspan='4'>Várakozás adatokra...</td></tr>
        </tbody>
    </table>
</div>

<script>
    let lastFirstId = null;

    async function fetchNewData() {
        const statusEl = document.getElementById('connection-status');
        const tableBody = document.getElementById('data-table-body');

        try {
            // Itt hívjuk meg a kombinált fájlt!
            const response = await fetch('insert_data_apikey.php');
            
            if (!response.ok) throw new Error('Hálózati hiba (data_manager.php nem elérhető)');
            
            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            if (data.length > 0) {
                let html = '';
                data.forEach((row, index) => {
                    // Megnézzük, jött-e új mérés az előző lekérés óta
                    const isNew = (lastFirstId !== null && row.id > lastFirstId);
                    const rowClass = (index === 0 && isNew) ? 'class="new-row"' : '';
                    
                    html += `<tr ${rowClass}>
                        <td>#${row.id}</td>
                        <td class="temp-val">${parseFloat(row.temperature).toFixed(2)} °C</td>
                        <td class="press-val">${parseFloat(row.pressure).toFixed(2)} hPa</td>
                        <td>${row.created_at}</td>
                    </tr>`;
                });

                lastFirstId = data[0].id; // Elmentjük a legfrissebb ID-t
                tableBody.innerHTML = html;
                
                statusEl.innerText = "Online";
                statusEl.className = "status-ok";
            } else {
                tableBody.innerHTML = "<tr><td colspan='4'>Nincsenek adatok az adatbázisban.</td></tr>";
            }

            document.getElementById('last-update').innerText = new Date().toLocaleTimeString();

        } catch (error) {
            console.error('Hiba történt:', error);
            statusEl.innerText = "Hiba: " + error.message;
            statusEl.className = "status-error";
        }
    }

    // Frissítés 3 másodpercenként (ezt állíthatod igény szerint)
    setInterval(fetchNewData, 3000);

    // Első futtatás azonnal
    fetchNewData();
</script>

</body>
</html>
