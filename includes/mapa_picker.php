<?php
require_once __DIR__ . '/../config/maps.php';

$latInicial = $dados['latitude'] !== null && $dados['latitude'] !== '' ? (float)$dados['latitude'] : -14.235004;
$lngInicial = $dados['longitude'] !== null && $dados['longitude'] !== '' ? (float)$dados['longitude'] : -51.925280;
$zoomInicial = ($dados['latitude'] !== null && $dados['latitude'] !== '') ? 15 : 4;
?>
<div class="form-group">
    <label for="busca-endereco">Localização no mapa</label>
    <input type="text" id="busca-endereco" class="map-search" placeholder="Buscar endereço, cidade ou ponto de referência...">
    <div id="mapa-picker" class="mapa-picker"></div>
    <p class="map-hint">Clique no mapa ou arraste o marcador para ajustar o ponto exato.</p>
    <input type="hidden" id="latitude" name="latitude" value="<?= htmlspecialchars((string)($dados['latitude'] ?? '')) ?>">
    <input type="hidden" id="longitude" name="longitude" value="<?= htmlspecialchars((string)($dados['longitude'] ?? '')) ?>">
</div>

<script>
    let mapaPicker, marcadorPicker, geocoderPicker;

    function initMapaPicker() {
        geocoderPicker = new google.maps.Geocoder();

        mapaPicker = new google.maps.Map(document.getElementById('mapa-picker'), {
            center: { lat: <?= json_encode($latInicial) ?>, lng: <?= json_encode($lngInicial) ?> },
            zoom: <?= json_encode($zoomInicial) ?>,
        });

        marcadorPicker = new google.maps.Marker({
            position: { lat: <?= json_encode($latInicial) ?>, lng: <?= json_encode($lngInicial) ?> },
            map: mapaPicker,
            draggable: true,
        });

        mapaPicker.addListener('click', (evento) => {
            posicionarMarcadorPicker(evento.latLng);
        });

        marcadorPicker.addListener('dragend', () => {
            posicionarMarcadorPicker(marcadorPicker.getPosition());
        });

        const inputBusca = document.getElementById('busca-endereco');
        const autocomplete = new google.maps.places.Autocomplete(inputBusca);
        autocomplete.bindTo('bounds', mapaPicker);
        autocomplete.addListener('place_changed', () => {
            const lugar = autocomplete.getPlace();
            if (!lugar.geometry) {
                return;
            }
            mapaPicker.setCenter(lugar.geometry.location);
            mapaPicker.setZoom(16);
            posicionarMarcadorPicker(lugar.geometry.location);
        });
    }

    function posicionarMarcadorPicker(latLng) {
        marcadorPicker.setPosition(latLng);
        marcadorPicker.setMap(mapaPicker);
        document.getElementById('latitude').value = latLng.lat();
        document.getElementById('longitude').value = latLng.lng();

        geocoderPicker.geocode({ location: latLng }, (resultados, status) => {
            if (status === 'OK' && resultados[0]) {
                document.getElementById('localizacao').value = resultados[0].formatted_address;
            }
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(GOOGLE_MAPS_API_KEY) ?>&libraries=places&callback=initMapaPicker" async defer></script>
