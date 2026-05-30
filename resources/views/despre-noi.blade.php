@extends('layouts.app')

@section('title', 'Despre noi | ReclamDesign Modern')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
<section class="section-shell about-page-shell">
    <div class="section-heading left-heading">
        <span>Despre noi</span>
        <h1>ReclamDesign Modern</h1>
        <p>Transformam produse simple in materiale promotionale personalizate: haine, cani, banere si articole de cancelarie.</p>
    </div>

    <div class="about-page-grid">
        <article class="about-info-card">
            <h2>Ce facem</h2>
            <p>Oferim produse pregatite pentru personalizare. Clientul poate incarca designul, il poate pozitiona pe produs, apoi il poate trimite in comanda.</p>
        </article>
        <article class="about-info-card">
            <h2>Fondarea</h2>
            <p>Aici poti adauga o mica istorie despre fondarea ReclamDesign Modern, cum a aparut ideea si ce valori reprezinta compania.</p>
        </article>
    </div>

    <div class="about-location-card">
        <div class="about-location-content">
            <span>Unde ne gasiti</span>
            <h2>Locatia noastra</h2>
            <p>Strada 31 August 1989 15a, MD-3909, Cahul, Republica Moldova</p>
            <div class="route-actions" aria-label="Optiuni traseu">
                <button type="button" id="show-route-button" class="route-button">
                    Traseu
                </button>
                <div
                    id="manual-route-person"
                    class="route-person"
                    draggable="true"
                    role="button"
                    tabindex="0"
                    aria-label="Trage omuletul pe harta pentru a alege punctul de pornire"
                    title="Trage-ma pe harta sau apasa, apoi alege punctul pe harta"
                >
                    <img src="{{ asset('assets/MAP_MAN/omul.png') }}" alt="" aria-hidden="true" class="route-person-img">
                </div>
            </div>
            <small id="route-message" class="route-message" aria-live="polite"></small>
        </div>
        <div class="about-map" aria-label="Harta locatie ReclamDesign Modern">
            <div id="reclam-location-map" class="about-map-canvas"></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapElement = document.getElementById('reclam-location-map');
        if (!mapElement || typeof L === 'undefined') return;

        var position = [45.905500, 28.196020];
        var address = '<strong>Reclam Design</strong><br>Strada 31 August 1989 15a,<br>MD-3909, Cahul, Republica Moldova';
        var routeButton = document.getElementById('show-route-button');
        var routePerson = document.getElementById('manual-route-person');
        var routeMessage = document.getElementById('route-message');
        var routeLine = null;
        var startMarker = null;
        var manualPlacementMode = false;

        var map = L.map(mapElement, {
            scrollWheelZoom: false
        }).setView(position, 18);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var organizationMarker = L.marker(position).addTo(map)
            .bindPopup(address, { closeButton: false })
            .openPopup();

        function setRouteMessage(message) {
            if (routeMessage) routeMessage.textContent = message || '';
        }

        function drawRouteLine(points) {
            if (routeLine) map.removeLayer(routeLine);

            routeLine = L.polyline(points, {
                color: '#e11d48',
                weight: 6,
                opacity: 0.95,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);

            map.fitBounds(routeLine.getBounds(), {
                padding: [32, 32],
                maxZoom: 18
            });
        }

        function drawDirectRoute(startPosition) {
            drawRouteLine([startPosition, position]);
            setRouteMessage('Traseul a fost afisat pe harta.');
        }

        function drawRoadRoute(startPosition) {
            var routeUrl = 'https://router.project-osrm.org/route/v1/driving/'
                + startPosition[1] + ',' + startPosition[0] + ';'
                + position[1] + ',' + position[0]
                + '?overview=full&geometries=geojson';

            fetch(routeUrl)
                .then(function (response) {
                    if (!response.ok) throw new Error('Route request failed');
                    return response.json();
                })
                .then(function (data) {
                    if (!data.routes || !data.routes.length) {
                        drawDirectRoute(startPosition);
                        return;
                    }

                    var routePoints = data.routes[0].geometry.coordinates.map(function (point) {
                        return [point[1], point[0]];
                    });

                    drawRouteLine(routePoints);
                    setRouteMessage('Traseul pana la organizatie a fost afisat.');
                })
                .catch(function () {
                    drawDirectRoute(startPosition);
                });
        }

        function setManualStartPoint(startPosition) {
            if (startMarker) map.removeLayer(startMarker);

            startMarker = L.marker(startPosition, {
                draggable: true,
                title: 'Punct de pornire'
            }).addTo(map).bindPopup('Punctul ales de tine');

            startMarker.on('dragend', function (event) {
                var markerPosition = event.target.getLatLng();
                drawRoadRoute([markerPosition.lat, markerPosition.lng]);
            });

            drawRoadRoute(startPosition);
            organizationMarker.openPopup();
        }

        function enableManualPlacementMode() {
            manualPlacementMode = true;
            if (routePerson) routePerson.classList.add('is-active');
            mapElement.classList.add('is-route-target');
            setRouteMessage('Apasa pe harta unde vrei sa fie punctul de pornire sau trage omuletul pe harta.');
        }

        function disableManualPlacementMode() {
            manualPlacementMode = false;
            if (routePerson) routePerson.classList.remove('is-active');
            mapElement.classList.remove('is-route-target');
        }

        function placeManualStartFromLatLng(latlng) {
            disableManualPlacementMode();
            setManualStartPoint([latlng.lat, latlng.lng]);
        }

        function getLatLngFromPagePoint(pageX, pageY) {
            var rect = mapElement.getBoundingClientRect();
            var containerPoint = L.point(pageX - rect.left - window.scrollX, pageY - rect.top - window.scrollY);
            return map.containerPointToLatLng(containerPoint);
        }

        function showRouteFromUserLocation() {
            if (!navigator.geolocation) {
                setRouteMessage('Browserul nu permite detectarea locatiei.');
                return;
            }

            if (routeButton) routeButton.disabled = true;
            setRouteMessage('Se cauta locatia curenta...');

            navigator.geolocation.getCurrentPosition(function (result) {
                var startPosition = [result.coords.latitude, result.coords.longitude];

                if (startMarker) map.removeLayer(startMarker);
                startMarker = L.circleMarker(startPosition, {
                    radius: 7,
                    color: '#e11d48',
                    fillColor: '#e11d48',
                    fillOpacity: 1,
                    weight: 2
                }).addTo(map).bindPopup('Locatia ta');

                drawRoadRoute(startPosition);
                organizationMarker.openPopup();
                if (routeButton) routeButton.disabled = false;
            }, function () {
                setRouteMessage('Nu am putut primi locatia. Activeaza permisiunea pentru locatie si apasa din nou pe Traseu.');
                if (routeButton) routeButton.disabled = false;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 30000
            });
        }

        if (routeButton) {
            routeButton.addEventListener('click', showRouteFromUserLocation);
        }

        if (routePerson) {
            routePerson.addEventListener('click', enableManualPlacementMode);
            routePerson.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    enableManualPlacementMode();
                }
            });
            routePerson.addEventListener('dragstart', function (event) {
                event.dataTransfer.setData('text/plain', 'manual-route-start');
                event.dataTransfer.effectAllowed = 'copy';
                setRouteMessage('Lasa omuletul pe harta in locul de unde vrei traseul.');
            });
        }

        mapElement.addEventListener('dragover', function (event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
        });

        mapElement.addEventListener('drop', function (event) {
            event.preventDefault();
            var latlng = getLatLngFromPagePoint(event.pageX, event.pageY);
            placeManualStartFromLatLng(latlng);
        });

        map.on('click', function (event) {
            if (!manualPlacementMode) return;
            placeManualStartFromLatLng(event.latlng);
        });

        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    });
</script>
@endpush
