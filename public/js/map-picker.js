(function () {
    'use strict';

    function setAddressValue(addr) {
        var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;

        function applyToInput(el) {
            setter.call(el, addr);
            el.dispatchEvent(new Event('input',  { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // 1-usul: Maxsus data-map-address atributli input (ProjectResource Edit/Create)
        document.querySelectorAll('input[data-map-address]').forEach(applyToInput);

        // 2-usul: Kanban board address input
        var kb = document.getElementById('kb-address-input');
        if (kb) applyToInput(kb);

        // 3-usul: wire:model.live="address" (Kanban)
        document.querySelectorAll('input[wire\\:model\\.live="address"]').forEach(applyToInput);
    }

    var _map = null, _placemark = null;

    function showStatus(msg) {
        var el = document.getElementById('bh-locate-status');
        if (!el) return;
        el.textContent = msg;
        el.style.display = msg ? 'block' : 'none';
        if (msg) setTimeout(function () { el.style.display = 'none'; }, 3000);
    }

    function setFieldValue(modelKey, value) {
        var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        var selectors = [
            'input[wire\\:model="data.' + modelKey + '"]',
            'input[wire\\:model\\.live="data.' + modelKey + '"]',
            'input[wire\\:model\\.defer="data.' + modelKey + '"]',
        ];
        selectors.forEach(function(sel) {
            document.querySelectorAll(sel).forEach(function(el) {
                setter.call(el, value);
                el.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    }

    function placeAt(coords) {
        if (!_map) return;
        if (_placemark) { _map.geoObjects.remove(_placemark); }
        _placemark = new ymaps.Placemark(coords, {}, { preset: 'islands#redDotIcon' });
        _map.geoObjects.add(_placemark);
        _map.setCenter(coords, 16);

        var lat = coords[0].toFixed(6);
        var lng = coords[1].toFixed(6);

        // Koordinatalarni DOM inputlarga (Filament form uchun)
        setFieldValue('latitude',  lat);
        setFieldValue('longitude', lng);

        // Birlashgan coord-picker inputini yangilash (Alpine.js orqali)
        window.dispatchEvent(new CustomEvent('bh-fill-coords', { detail: { lat: lat, lng: lng } }));

        // fp-lat/lng yashirin inputlarini ham yangilash
        var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        ['fp-lat-input', 'fp-lng-input'].forEach(function(id, i) {
            var el = document.getElementById(id);
            if (el) { setter.call(el, i === 0 ? lat : lng); el.dispatchEvent(new Event('input',{bubbles:true})); }
        });

        ymaps.geocode(coords, { results: 1 }).then(function (res) {
            var obj  = res.geoObjects.get(0);
            var addr = obj ? obj.getAddressLine() : (lat + ', ' + lng);

            // 1-usul: $wire.call() — to'g'ridan Livewire PHP metod chaqirish
            var called = false;
            if (window.Livewire) {
                document.querySelectorAll('[wire\\:id]').forEach(function(el) {
                    try {
                        var comp = window.Livewire.find(el.getAttribute('wire:id'));
                        if (comp && comp.$wire) {
                            comp.$wire.call('mapAddressSelected', addr, lat, lng)
                                .catch(function(){});
                            called = true;
                        }
                    } catch(e) {}
                });
            }

            // 2-usul: DOM fallback (Kanban Board uchun + zaxira)
            setAddressValue(addr);
        });
    }

    window.bhLoadYandexUrl = function () {
        var input = document.getElementById('bh-yandex-url');
        if (!input) return;
        var url = input.value.trim();
        if (!url) { showStatus('URL kiriting'); return; }

        var lat = null, lng = null;

        // 1-usul: whatshere[point]=lng,lat
        var wpMatch = url.match(/whatshere(?:%5B|\[)point(?:%5D|\])=([0-9.\-]+)%2C([0-9.\-]+)/i)
                   || url.match(/whatshere\[point\]=([0-9.\-]+),([0-9.\-]+)/i);
        if (wpMatch) {
            lng = parseFloat(wpMatch[1]);
            lat = parseFloat(wpMatch[2]);
        }

        // 2-usul: ll=lng,lat
        if (!lat) {
            var llMatch = url.match(/[?&]ll=([0-9.\-]+)%2C([0-9.\-]+)/i)
                       || url.match(/[?&]ll=([0-9.\-]+),([0-9.\-]+)/i);
            if (llMatch) {
                lng = parseFloat(llMatch[1]);
                lat = parseFloat(llMatch[2]);
            }
        }

        // 3-usul: /maps/NNN,NNN (eski format)
        if (!lat) {
            var coordMatch = url.match(/maps\/@?([0-9.\-]+),([0-9.\-]+)/);
            if (coordMatch) {
                lat = parseFloat(coordMatch[1]);
                lng = parseFloat(coordMatch[2]);
            }
        }

        if (!lat || !lng || isNaN(lat) || isNaN(lng)) {
            showStatus('URL dan koordinata topilmadi');
            return;
        }

        showStatus('Manzil yuklanmoqda…');
        input.value = '';

        if (!_map) {
            loadYandexMaps();
            setTimeout(function() { placeAt([lat, lng]); }, 1200);
        } else {
            placeAt([lat, lng]);
        }
    };

    window.bhLocateMe = function () {
        if (!navigator.geolocation) {
            showStatus('Brauzer geolokatsiyani qo\'llab-quvvatlamaydi');
            return;
        }
        var btn = document.getElementById('bh-locate-btn');
        if (btn) { btn.disabled = true; btn.style.opacity = '0.6'; }
        showStatus('Joylashuv aniqlanmoqda…');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
                showStatus('Joylashuv aniqlandi ✓');
                placeAt([pos.coords.latitude, pos.coords.longitude]);
            },
            function (err) {
                if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
                var msg = err.code === 1 ? 'Ruxsat berilmadi — brauzer sozlamalarini tekshiring'
                        : err.code === 2 ? 'Joylashuv aniqlanmadi'
                        : 'Vaqt tugadi, qayta urinib ko\'ring';
                showStatus(msg);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    };

    function initMap() {
        var container = document.getElementById('yandex-map-container');
        if (!container || container.dataset.mapReady) return;
        container.dataset.mapReady = '1';

        // Edit rejimida mavjud koordinatalarni olamiz
        var wrapper = container.closest('[data-lat]') || container.parentElement;
        var existLat = parseFloat(wrapper && wrapper.dataset.lat);
        var existLng = parseFloat(wrapper && wrapper.dataset.lng);
        var hasCoords = !isNaN(existLat) && !isNaN(existLng) && existLat !== 0;

        _map = new ymaps.Map(container, {
            center: hasCoords ? [existLat, existLng] : [41.2995, 69.2401],
            zoom:   hasCoords ? 16 : 12,
            controls: ['zoomControl', 'searchControl']
        });
        _placemark = null;

        // Mavjud koordinatani marker bilan ko'rsatish
        if (hasCoords) {
            _placemark = new ymaps.Placemark([existLat, existLng], {}, { preset: 'islands#redDotIcon' });
            _map.geoObjects.add(_placemark);
        }

        _map.events.add('click', function (e) {
            placeAt(e.get('coords'));
        });
    }

    function loadYandexMaps() {
        if (!document.getElementById('yandex-map-container')) return;

        if (typeof ymaps !== 'undefined') {
            ymaps.ready(initMap);
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://api-maps.yandex.ru/2.1/?lang=uz_UZ';
        script.onload = function () { ymaps.ready(initMap); };
        document.head.appendChild(script);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadYandexMaps);
    } else {
        loadYandexMaps();
    }

    document.addEventListener('livewire:navigated', function () {
        var c = document.getElementById('yandex-map-container');
        if (c) { delete c.dataset.mapReady; _map = null; _placemark = null; loadYandexMaps(); }
    });
})();
