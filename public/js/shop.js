/* eslint-env browser */
(function () {
    'use strict';

    // ---------- Map ----------
    var map = L.map('map', { attributionControl: false }).setView([52.6088, 39.5994], 12);
    L.tileLayer('https://tile.openstreetmap.de/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    var marker = null;
    map.on('click', function (e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        if (marker) { marker.setLatLng(e.latlng); } else { marker = L.marker(e.latlng).addTo(map); }
        fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&accept-language=ru')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var addr = '';
                var a = data.address || {};
                if (a.road) addr += a.road;
                if (a.house_number) addr += ', ' + a.house_number;
                if (a.city || a.town || a.village) addr += ', ' + (a.city || a.town || a.village);
                document.getElementById('address').value = addr || data.display_name;
                marker.bindPopup(addr || data.display_name).openPopup();
            })
            .catch(function () {
                document.getElementById('address').value = lat.toFixed(5) + ', ' + lng.toFixed(5);
            });
    });

    // ---------- Cart ----------
    var cart = [];

    function addToCart(id, name, price) {
        var size = document.getElementById('size-' + id).value;
        var qty = parseInt(document.getElementById('qty-' + id).value, 10);
        if (isNaN(qty) || qty < 1) qty = 1;
        var existing = cart.find(function (i) { return i.id === id && i.size === size; });
        if (existing) { existing.qty = qty; } else { cart.push({ id: id, name: name, size: size, qty: qty, price: price }); }
        var card = document.getElementById('card-' + id);
        if (card) {
            card.classList.add('selected');
            var btn = card.querySelector('.btn-add-cart');
            btn.textContent = '✓ В корзине';
            btn.classList.add('in-cart');
        }
        renderCart();
    }

    function removeFromCart(index) {
        var item = cart[index];
        cart.splice(index, 1);
        var stillInCart = cart.find(function (i) { return i.id === item.id; });
        if (!stillInCart) {
            var card = document.getElementById('card-' + item.id);
            if (card) {
                card.classList.remove('selected');
                var btn = card.querySelector('.btn-add-cart');
                btn.textContent = 'В корзину';
                btn.classList.remove('in-cart');
            }
        }
        renderCart();
    }

    function renderCart() {
        var list = document.getElementById('cart-list');
        var total = document.getElementById('cart-total');
        var sum = document.getElementById('cart-total-sum');
        if (cart.length === 0) {
            list.innerHTML = '<li><span class="cart-empty-msg">Корзина пуста — выберите товары выше</span></li>';
            total.style.display = 'none';
            return;
        }
        var totalSum = 0;
        list.innerHTML = '';
        cart.forEach(function (item, index) {
            var itemSum = item.price * item.qty;
            totalSum += itemSum;
            var li = document.createElement('li');
            var info = document.createElement('span');
            info.textContent = item.name + ' / ' + item.size + ' / ' + item.qty + ' шт.';
            li.appendChild(info);
            var rightWrap = document.createElement('span');
            var price = document.createElement('b');
            price.textContent = itemSum.toLocaleString('ru-RU') + ' ₽';
            rightWrap.appendChild(price);
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'cart-remove';
            remove.title = 'Удалить';
            remove.textContent = '✕';
            remove.addEventListener('click', function () { removeFromCart(index); });
            rightWrap.appendChild(remove);
            li.appendChild(rightWrap);
            list.appendChild(li);
        });
        sum.textContent = totalSum.toLocaleString('ru-RU');
        total.style.display = 'block';
    }

    // Wire buttons
    document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            addToCart(
                parseInt(btn.dataset.productId, 10),
                btn.dataset.productName,
                parseInt(btn.dataset.productPrice, 10),
            );
        });
    });

    // Inject cart contents into Symfony CollectionType on submit.
    window.injectCartItems = function () {
        if (cart.length === 0) {
            alert('Добавьте хотя бы один товар в корзину!');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return false;
        }
        var collection = document.getElementById('cart-items-collection');
        var prototype = collection.dataset.prototype;
        // Wipe any previously injected rows so resubmits stay consistent.
        collection.innerHTML = '';
        var index = 0;
        var totalSum = 0;
        cart.forEach(function (item) {
            totalSum += item.price * item.qty;
            var html = prototype.replace(/__name__/g, String(index));
            var wrap = document.createElement('div');
            wrap.innerHTML = html;
            wrap.querySelectorAll('input').forEach(function (input) {
                if (/\[category]$/.test(input.name)) input.value = item.name;
                else if (/\[size]$/.test(input.name)) input.value = item.size;
                else if (/\[quantity]$/.test(input.name)) input.value = item.qty;
            });
            collection.appendChild(wrap);
            index += 1;
        });
        document.getElementById('total_sum').value = totalSum;
        return true;
    };
})();
