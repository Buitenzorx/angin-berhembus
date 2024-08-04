<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            background-color: rgb(255, 255, 255);
            color: rgb(14, 2, 2);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #map {
            height: 400px;
        }
        #terminal {
            background-color: #333;
            color: #00FF00;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', Courier, monospace;
        }
        #gauge {
            height: 200px;
            width: 100%;
            margin-top: 20px;
        }
        .battery-box {
            width: 100%;
            background-color: #333;
            color: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .battery-box div {
            margin: 0 10px;
        }
        .battery-box span {
            font-weight: bold;
        }
        .navbar-custom {
            background-color: #333;
        }
        .navbar-custom .navbar-nav .nav-link {
            color: white;
        }
        .navbar-custom .navbar-nav .nav-link:hover {
            color: #00FF00;
        }
        .history-list {
            max-height: 200px;
            overflow-y: auto;
            background-color: #0e0303;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #fff; /* Ensure text color is readable */
        }
        .gauge-status {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <a class="navbar-brand" href="#">Angin Berhembus</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/history">History</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid flex-grow-1">
        <div class="row">
            <div class="col-12 mt-6">
                <h1 class="text-center my-4">Monitoring Motor Hydrogen </h1>
            </div>
        </div>
        <div class="row">
            <div class="col-8 mt-4">
                <div class="battery-box">
                    <div>Voltage: <span id="voltage">{{ $data->voltage ?? 'N/A' }}</span></div>
                    <div>Current: <span id="current">{{ $data->current ?? 'N/A' }}</span></div>
                    <div>Power: <span id="power">{{ $data->power ?? 'N/A' }}</span></div>
                </div>
                <div id="map"></div>
            </div>
            <div class="col-md-4 mt-4">
                <div id="terminal">
                    <h4>Coordinate History</h4>
                    <hr style="background-color: white"></hr>
                    <div class="history-list" id="coordinate-list">
                        @foreach ($history as $entry)
                            <div>Location: ({{ $entry->latitude }}, {{ $entry->longitude }}) - {{ $entry->created_at->format('Y-m-d H:i:s') }} WIB</div>
                        @endforeach
                    </div>
                </div>
                <div class="col md-6 ">
                    <div id="gauge"></div>
                    <div class="gauge-status" id="gauge-status">Status: <span id="status">-</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/justgage/1.4.0/justgage.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize gauge
            var gauge = new JustGage({
                id: "gauge",
                value: {{ $data->gas_level ?? 0 }},
                min: 0,
                max: 1000,
                title: "Gas Level",
                gaugeWidthScale: 0.3,
                customSectors: [
                    { color: "#00FF00", lo: 0, hi: 200 },
                    { color: "#FFFF00", lo: 200, hi: 600 },
                    { color: "#FF0000", lo: 600, hi: 1000 }
                ]
            });

            // Initialize map
            var map = L.map('map').setView([{{ $data->latitude ?? 0 }}, {{ $data->longitude ?? 0 }}], 13);

            // Add tile layer from OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Create a marker
            var marker = L.marker([{{ $data->latitude ?? 0 }}, {{ $data->longitude ?? 0 }}]).addTo(map)
                .bindPopup('Current Location')
                .openPopup();

            // Function to update gauge, map, and coordinate history with new data
            function updateData() {
                $.get('angin-berhembus/public/api/sensor-data/latest', function(data) {
                    // Update gauge
                    gauge.refresh(data.gas_level);
                    $('#voltage').text(data.voltage);
                    $('#current').text(data.current);
                    $('#power').text(data.power);

                    // Update map
                    var latLng = [data.latitude, data.longitude];
                    marker.setLatLng(latLng)
                        .bindPopup('Current Location: (' + data.latitude + ', ' + data.longitude + ')')
                        .openPopup();
                    map.setView(latLng, 13);

                    // Update coordinate history
                    var coordinateList = document.getElementById('coordinate-list');
                    var coordItem = document.createElement('div');
                    var currentTime = new Date();
                    var timeWIB = new Date(currentTime.getTime() + 7 * 60 * 60 * 1000); // Convert to WIB
                    var formattedTime = timeWIB.toISOString().replace('T', ' ').substr(0, 19) + ' WIB';
                    coordItem.textContent = `Location: (${data.latitude}, ${data.longitude}) - ${formattedTime}`;
                    coordinateList.insertBefore(coordItem, coordinateList.firstChild);

                    // Update gauge status
                    var statusElement = document.getElementById('status');
                    var statusText;
                    if (data.gas_level >= 0 && data.gas_level <= 250) {
                        statusText = "Aman";
                    } else if (data.gas_level >= 251 && data.gas_level <= 500) {
                        statusText = "Gas Bocor";
                    } else if (data.gas_level >= 501 && data.gas_level <= 700) {
                        statusText = "Bahaya";
                    } else if (data.gas_level >= 701 && data.gas_level <= 1000) {
                        statusText = "Gas Mengalami Kebocoran, Bahaya!";
                    } else {
                        statusText = "Unknown";
                    }
                    statusElement.textContent = statusText;
                });
            }

            // Update data every 5 seconds
            setInterval(updateData, 5000);
        });
    </script>
</body>
</html>
