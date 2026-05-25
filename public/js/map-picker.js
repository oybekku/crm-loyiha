(function () {
    'use strict';

    function setAddressValue(addr) {
        // 1-usul: Livewire JS API orqali (eng ishonchli)
        if (window.Livewire) {
            var wireEl = document.querySelector('[wire\\:id]');
            if (wireEl) {
                var comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
                if (comp) {
                    comp.set('data.address', addr);
                    return;
                }
            }
        }

        // 2-usul: To'g'ridan-to'g'ri input elementini topish
        var inputs = document.querySelectorAll('input[wire\\:model="data.address"], input[wire\\:model\\.live="data.address"]');
        if (inputs.length > 0) {
            var input = inputs[0];
            var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
            nativeInputValueSetter.call(input, addr);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            return;
        }

        // 3-usul: Barcha inputlardan 'address' nomlilarini qidirish
        var allInputs = document.querySelectorAll('input[type="text"]');
        allInputs.forEach(function (input) {
            var model = input.getAttribute('wire:model') || input.getAttribute('wire:model.live') || '';
            if (model.includes('address')) {
                var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeInputValueSetter.call(input, addr);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    function initMap() {
        var container = document.getElementById('yandex-map-container');
        if (!container || container.dataset.mapReady) return;
        container.dataset.mapReady = '1';

        var map = new ymaps.Map(container, {
            center: [41.2995, 69.2401],
            zoom: 12,
            controls: ['zoomControl', 'searchControl', 'geolocationControl']
        });

        var placemark = null;

        map.events.add('click', function (e) {
            var coords = e.get('coords');
            if (placemark) { map.geoObjects.remove(placemark); }
            placemark = new ymaps.Placemark(coords, {}, { preset: 'islands#redDotIcon' });
            map.geoObjects.add(placemark);

            ymaps.geocode(coords, { results: 1 }).then(function (res) {
                var obj = res.geoObjects.get(0);
                if (!obj) return;
                setAddressValue(obj.getAddressLine());
            });
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
        if (c) { delete c.dataset.mapReady; loadYandexMaps(); }
    });
})();
