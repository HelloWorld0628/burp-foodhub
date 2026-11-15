const Auth = {
    cachedAuth: null,

    async check() {
        try {
            const res = await fetch('customer_auth.php?action=check');
            const data = await res.json();
            this.cachedAuth = data;
            return data;
        } catch (err) {
            console.error('Auth check failed:', err);
            this.cachedAuth = { success: true, logged_in: false };
            return this.cachedAuth;
        }
    },

    async login(email, password) {
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', email);
        formData.append('password', password);

        const res = await fetch('customer_auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            this.cachedAuth = { success: true, logged_in: true, customer: data.customer };
        }
        return data;
    },

    async register(name, email, password, mobile, location) {
        const formData = new FormData();
        formData.append('action', 'register');
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('mobile', mobile);
        formData.append('location', location);

        const res = await fetch('customer_auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            this.cachedAuth = { success: true, logged_in: true, customer: data.customer };
        }
        return data;
    },

    async logout() {
        const formData = new FormData();
        formData.append('action', 'logout');
        await fetch('customer_auth.php', { method: 'POST', body: formData });
        this.cachedAuth = { success: true, logged_in: false };
    },

    isLoggedIn() {
        return this.cachedAuth?.logged_in === true;
    },

    getCustomer() {
        return this.cachedAuth?.customer || null;
    },

    async requireAuth(redirectTo = null) {
        if (!this.cachedAuth) {
            await this.check();
        }
        if (!this.isLoggedIn()) {
            const target = redirectTo || window.location.pathname.split('/').pop();
            window.location.href = `login.html?redirect=${encodeURIComponent(target)}`;
            return false;
        }
        return true;
    },

    updateNav() {
        const navAuth = document.getElementById('navAuth');
        if (!navAuth) return;

        if (this.isLoggedIn()) {
            const customer = this.getCustomer();
            navAuth.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="color: #fff;">Hi, ${customer.customer_name}</span>
                    <button class="btn" id="logoutBtn" style="padding: 8px 16px;">Logout</button>
                </div>
            `;
            document.getElementById('logoutBtn')?.addEventListener('click', () => this.handleLogout());
        } else {
            navAuth.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="login.html" class="btn" style="padding: 8px 16px;">Login</a>
                    <a href="register.html" class="btn" style="padding: 8px 16px; background: transparent; border: 2px solid orangered;">Register</a>
                </div>
            `;
        }
    },

    async handleLogout() {
        await this.logout();
        this.updateNav();
        if (window.location.pathname.includes('checkout.html') || window.location.pathname.includes('menu.html')) {
            window.location.href = 'index.html';
        }
    },

    showLoginPrompt(message = 'Please login to add items to cart') {
        return new Promise((resolve) => {
            const existing = document.getElementById('authPromptModal');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'authPromptModal';
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); display: flex; justify-content: center; align-items: center;
                z-index: 10000;
            `;
            modal.innerHTML = `
                <div style="background: #150d0d; border: 2px solid orangered; border-radius: 10px; padding: 30px; max-width: 400px; width: 90%; text-align: center;">
                    <h3 style="color: orangered; margin-bottom: 15px;">Login Required</h3>
                    <p style="color: #ccc; margin-bottom: 25px;">${message}</p>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button class="btn" id="promptLogin" style="flex: 1;">Login</button>
                        <button class="btn" id="promptRegister" style="flex: 1; background: transparent; border: 2px solid orangered;">Register</button>
                        <button class="btn" id="promptCancel" style="flex: 1; background: #333;">Cancel</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            document.getElementById('promptLogin').onclick = () => { modal.remove(); resolve('login'); };
            document.getElementById('promptRegister').onclick = () => { modal.remove(); resolve('register'); };
            document.getElementById('promptCancel').onclick = () => { modal.remove(); resolve('cancel'); };
        });
    }
};

window.addEventListener('scroll', function(){
    let navbar = document.querySelector('.navbar');
    if(this.window.scrollY > 20){
        navbar.classList.add('scrolled')
    }else{
        navbar.classList.remove('scrolled')
    }
});

const toTopBtn = document.querySelector('#to-top');
if (toTopBtn) {
    toTopBtn.addEventListener('click',()=>{
        let TopInterval = setInterval(()=>{
            let ArrowTop = document.body.scrollTop > 0 ? document.body : document.documentElement;
            if(ArrowTop.scrollTop > 0){
                ArrowTop.scrollTop = ArrowTop.scrollTop - 50;
            }
            if(ArrowTop.scrollTop < 1){
                clearInterval(TopInterval)
            }
        },10)
    }, false);
}

function showscroll(){
    let TopButton = document.getElementById('to-top');
    if(!TopButton) return;
    if(document.body.scrollTop > 100 || document.documentElement.scrollTop > 100){
        TopButton.classList.add('show')
    }else{
        TopButton.classList.remove('show')
    }
}

window.onscroll = () =>{
    showscroll();
}

const menuBtn = document.getElementById('menu_btn');
const navLinks = document.getElementById('nav_links');
const menuIcon = document.querySelector('i');

if (menuBtn) {
    menuBtn.addEventListener('click',(e)=>{
        navLinks.classList.toggle('open')
        const isOpen = navLinks.classList.contains('open')
        menuIcon.setAttribute('class', isOpen ? 'ri-close-line' : 'ri-menu-line')
    })
}

// ----------------------------------------------------
// FOOD DATA & CART LOGIC
// ----------------------------------------------------
let foodData = [];

const menuFoods = document.getElementById('menuFoods');
const bestSellerFoods = document.getElementById('bestSellerFoods');
const filterBtns = document.querySelectorAll('.filter-btn');
const cartBtn = document.getElementById('cartBtn');
const cartModal = document.getElementById('cartModal');
const closeCart = document.getElementById('closeCart');
const cartItemsContainer = document.getElementById('cartItems');
const cartTotalEl = document.getElementById('cartTotal');
const cartCountEls = document.querySelectorAll('.cart-count');

const BEST_SELLER_IDS = [1, 3, 57];
let cart = [];

function loadCartFromStorage() {
    const urlParams = new URLSearchParams(window.location.search);
    const cParam = urlParams.get('c');

    if (cParam) {
        const itemStrings = cParam.split('_');
        itemStrings.forEach(str => {
            const parts = str.split('-');
            const id = parseInt(parts[0]);
            const qty = parseInt(parts[1]);
            const food = foodData.find(f => f.menu_id === id || f.id === id);
            if (food && qty > 0) {
                cart.push({ ...food, id: food.menu_id || food.id, quantity: qty });
            }
        });
        try { localStorage.setItem('burpCart', JSON.stringify(cart)); } catch(e) {}
    } else {
        try {
            cart = JSON.parse(localStorage.getItem('burpCart')) || [];
        } catch(e) {
            cart = [];
        }
    }
}

function renderMenu(category = 'all') {
    if(!menuFoods) return;
    menuFoods.innerHTML = '';
    const filtered = category === 'all' ? foodData : foodData.filter(food => food.category === category);
    if (filtered.length === 0) {
        menuFoods.innerHTML = '<p style="grid-column: span 3; text-align: center; color: #888;">No items found.</p>';
        return;
    }
    filtered.forEach(food => {
        const foodId = food.menu_id || food.id;
        const card = document.createElement('div');
        card.className = 'food-card';
        card.innerHTML = `
            <img src="${food.image}" alt="${food.name}" onerror="this.src='https://via.placeholder.com/300x200/150d0d/ff4500?text=${encodeURIComponent(food.name)}'">
            <div class="food-card-info">
                <h4>${food.name}</h4>
                <p>${food.description}</p>
                <div class="price">₱${parseFloat(food.price).toFixed(2)}</div>
                <button class="btn" onclick="addToCart(${foodId})">Add to Cart</button>
            </div>
        `;
        menuFoods.appendChild(card);
    });
}

function renderBestSellers() {
    if (!bestSellerFoods) return;
    bestSellerFoods.innerHTML = '';
    const bestSellers = foodData.filter(food => BEST_SELLER_IDS.includes(food.menu_id || food.id));
    bestSellers.forEach(food => {
        const card = document.createElement('div');
        card.className = 'food-card';
        card.innerHTML = `
            <img src="${food.image}" alt="${food.name}" onerror="this.src='https://via.placeholder.com/300x200/150d0d/ff4500?text=${encodeURIComponent(food.name)}'">
            <div class="food-card-info">
                <h4>${food.name}</h4>
                <p>${food.description}</p>
                <div class="price">₱${parseFloat(food.price).toFixed(2)}</div>
                <button class="btn" onclick="window.location.href='menu.html'">Order Now</button>
            </div>
        `;
        bestSellerFoods.appendChild(card);
    });
}

window.addToCart = async function(id) {
    if (!Auth.isLoggedIn()) {
        const action = await Auth.showLoginPrompt('Please login to add items to your cart');
        if (action === 'login') {
            window.location.href = `login.html?redirect=menu.html`;
        } else if (action === 'register') {
            window.location.href = `register.html?redirect=menu.html`;
        }
        return;
    }

    const food = foodData.find(item => (item.menu_id || item.id) === id);
    if (!food) return;
    const existing = cart.find(item => item.id === id);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ ...food, id: food.menu_id || food.id, quantity: 1 });
    }

    saveCart();
    updateCartUI();

    if (cartBtn) {
        cartBtn.style.backgroundColor = 'orangered';
        setTimeout(() => { cartBtn.style.backgroundColor = 'transparent'; }, 300);
    }
};

window.updateQuantity = function(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) cart = cart.filter(c => c.id !== id);
        saveCart();
        updateCartUI();
        renderCheckoutSummary();
    }
};

window.removeFromCart = function(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    updateCartUI();
    renderCheckoutSummary();
};

function saveCart() {
    try { localStorage.setItem('burpCart', JSON.stringify(cart)); } catch(e) {}
}

function updateCartUI() {
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountEls.forEach(el => el.textContent = count);

    if (!cartItemsContainer) return;

    cartItemsContainer.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        cartItemsContainer.innerHTML = '<p style="color: #888; text-align:center;">Your cart is empty</p>';
    } else {
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            cartItemsContainer.innerHTML += `
                <div class="cart-item">
                    <div>
                        <h4>${item.name}</h4>
                        <p>₱${parseFloat(item.price).toFixed(2)} x ${item.quantity}</p>
                        <div style="margin-top: 5px;">
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span style="margin: 0 10px;">${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: orangered; font-weight: bold; font-size: 1.1rem;">₱${itemTotal.toFixed(2)}</div>
                        <button class="remove-btn" onclick="removeFromCart(${item.id})">Remove</button>
                    </div>
                </div>
            `;
        });
    }
    if (cartTotalEl) cartTotalEl.textContent = total.toFixed(2);
}

if(cartBtn && cartModal) {
    cartBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!Auth.isLoggedIn()) {
            const action = await Auth.showLoginPrompt('Please login to view your cart');
            if (action === 'login') {
                window.location.href = `login.html?redirect=menu.html`;
            } else if (action === 'register') {
                window.location.href = `register.html?redirect=menu.html`;
            }
            return;
        }
        cartModal.style.display = 'flex';
    });
}
if(closeCart && cartModal) {
    closeCart.addEventListener('click', () => cartModal.style.display = 'none');
}
window.addEventListener('click', (e) => {
    if (e.target === cartModal) cartModal.style.display = 'none';
});

const checkoutBtn = document.getElementById('checkoutBtn');
if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
            alert("Your cart is empty!");
            return;
        }
        const cartStr = cart.map(item => item.id + '-' + item.quantity).join('_');
        window.location.href = 'checkout.html?c=' + cartStr;
    });
}

function renderCheckoutSummary() {
    const checkoutItems = document.getElementById('checkoutItems');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const qrTotal = document.getElementById('qrTotal');

    if (!checkoutItems || !checkoutTotal) return;

    checkoutItems.innerHTML = '';
    let subtotal = 0;
    const deliveryFee = 30.00;

    if (cart.length === 0) {
        checkoutItems.innerHTML = '<p style="color: #888;">Your cart is empty. Please add some food first!</p>';
        checkoutTotal.textContent = "0.00";
        if(qrTotal) qrTotal.textContent = "0.00";
        return;
    }

    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        checkoutItems.innerHTML += `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 10px;">
                <span style="color: #fff;">${item.quantity}x ${item.name}</span>
                <span style="color: orangered;">₱${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });

    checkoutItems.innerHTML += `
        <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 10px;">
            <span style="color: #aaa; font-size: 1rem;">Subtotal</span>
            <span style="color: #aaa; font-size: 1rem;">₱${subtotal.toFixed(2)}</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <span style="color: #aaa; font-size: 1rem;">Delivery Fee</span>
            <span style="color: orangered; font-size: 1rem;">+ ₱${deliveryFee.toFixed(2)}</span>
        </div>
    `;

    let grandTotal = subtotal + deliveryFee;
    checkoutTotal.textContent = grandTotal.toFixed(2);
    if(qrTotal) qrTotal.textContent = grandTotal.toFixed(2);
}

const checkoutForm = document.getElementById('checkoutForm');
const payBtns = document.querySelectorAll('.pay-btn');
const selectedPayment = document.getElementById('selectedPayment');
const qrModal = document.getElementById('qrModal');
const closeQr = document.getElementById('closeQr');

if (payBtns.length > 0) {
    payBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            payBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
            selectedPayment.value = btn.getAttribute('data-method');
        });
    });
}

if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
        e.preventDefault();
        if (cart.length === 0) { alert("Your cart is empty!"); return; }
        if (!selectedPayment.value) { alert("Please select a payment method."); return; }

        const qrImage = document.getElementById('qrImage');
        if (qrImage) {
            qrImage.src = selectedPayment.value === 'gcash' ? 'img/gcash.png' : 'img/maya.png';
        }
        qrModal.style.display = 'flex';
    });
}

if (closeQr) {
    closeQr.addEventListener('click', () => { qrModal.style.display = 'none'; });
}

const proceedBtn = document.getElementById('proceedBtn');
const uploadModal = document.getElementById('uploadModal');
const closeUpload = document.getElementById('closeUpload');
const receiptUpload = document.getElementById('receiptUpload');
const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');

if (proceedBtn) {
    proceedBtn.addEventListener('click', () => {
        document.getElementById('qrModal').style.display = 'none';
        uploadModal.style.display = 'flex';
    });
}
if (closeUpload) {
    closeUpload.addEventListener('click', () => { uploadModal.style.display = 'none'; });
}
if (receiptUpload) {
    receiptUpload.addEventListener('change', function() {
        confirmPaymentBtn.style.display = this.files && this.files.length > 0 ? 'block' : 'none';
    });
}
if (confirmPaymentBtn) {
    confirmPaymentBtn.addEventListener('click', () => {
        const file = receiptUpload.files[0];
        if (!file) return alert("Please select an image first.");

        confirmPaymentBtn.textContent = "Processing...";
        confirmPaymentBtn.disabled = true;

        const customer = Auth.getCustomer();
        const formData = new FormData();
        formData.append('fullName', document.getElementById('fullName').value);
        formData.append('location', document.getElementById('location').value);
        formData.append('mobile', document.getElementById('mobile').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('notes', document.getElementById('notes').value);
        formData.append('paymentMethod', document.getElementById('selectedPayment').value);
        formData.append('cartData', JSON.stringify(cart));
        formData.append('totalPrice', document.getElementById('checkoutTotal').textContent);
        formData.append('receipt', file);
        if (customer) {
            formData.append('customer_id', customer.customer_id);
        }

        fetch('../src/process_order.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            alert(data);
            cart = [];
            saveCart();
            window.location.href = 'index.html';
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
            confirmPaymentBtn.textContent = "Confirm Payment";
            confirmPaymentBtn.disabled = false;
        });
    });
}

async function initAuthAndNav() {
    await Auth.check();
    Auth.updateNav();
}

document.addEventListener('DOMContentLoaded', async () => {
    await initAuthAndNav();

    fetch('../src/get_menu.php')
        .then(res => res.json())
        .then(data => {
            foodData = data;

            loadCartFromStorage();

            if (document.getElementById('menuFoods')) renderMenu('all');
            if (document.getElementById('bestSellerFoods')) renderBestSellers();

            updateCartUI();
            renderCheckoutSummary();

            if (filterBtns.length > 0) {
                filterBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        filterBtns.forEach(b => {
                            b.classList.remove('active');
                            b.style.background = 'transparent';
                        });
                        e.target.classList.add('active');
                        e.target.style.background = 'orangered';
                        renderMenu(e.target.dataset.category);
                    });
                });
            }
        })
        .catch(err => {
            console.error('Failed to load menu from database:', err);
            if (menuFoods) menuFoods.innerHTML = '<p style="grid-column:span 3; text-align:center; color:#888;">Could not load menu. Please refresh the page.</p>';
            if (bestSellerFoods) bestSellerFoods.innerHTML = '<p style="text-align:center; color:#888;">Could not load items.</p>';
        });
});