<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---------------------------------------------------------
    // 1. User Dropdown (Desktop)
    // ---------------------------------------------------------
    const userMenu = document.getElementById('userMenu');
    const userBtn = document.getElementById('userBtn');

    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenu.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }

    // ---------------------------------------------------------
    // 2. Desktop Categories Mega Dropdown Toggle
    // ---------------------------------------------------------
    const categoriesDropdown = document.getElementById('categoriesDropdown');
    const categoriesDropdownIcon = document.getElementById('categoriesDropdownIcon');

    if (categoriesDropdown && categoriesDropdownIcon) {
        categoriesDropdownIcon.addEventListener('click', function (e) {
            e.stopPropagation();
            categoriesDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (!categoriesDropdown.contains(e.target)) {
                categoriesDropdown.classList.remove('active');
            }
        });
    }

    // ---------------------------------------------------------
    // 3. Mobile Sidebar Drawer & Overlay (Smooth & Body-locked)
    // ---------------------------------------------------------
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileMenuClose = document.getElementById('mobileMenuClose');

    function openMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.add('active');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Lock background scrolling on phone
    }

    function closeMobileMenu() {
        if (!mobileMenu) return;
        mobileMenu.classList.remove('active');
        if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Unlock background scrolling
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openMobileMenu);
    if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
    if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMobileMenu);

    // ---------------------------------------------------------
    // 4. Mobile Category Accordion Toggle
    // ---------------------------------------------------------
    const mobileCatDropdown = document.getElementById('mobileCategoriesDropdown');
    if (mobileCatDropdown) {
        const trigger = mobileCatDropdown.querySelector('> a');
        if (trigger) {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                mobileCatDropdown.classList.toggle('active');
            });
        }
    }

    // ---------------------------------------------------------
    // 5. Search Bar Animated Placeholder
    // ---------------------------------------------------------
    const searchInput = document.getElementById('search-input');
    const placeholder = document.getElementById('animated-placeholder');

    if (searchInput && placeholder) {
        const suggestions = [
            @foreach($searchSuggestions ?? ['Fashion', 'Electronics', 'Mobiles', 'Home & Kitchen', 'Beauty', 'Sports'] as $s)
                "Search \"{{ $s }}\"",
            @endforeach
        ];

        let index = 0;
        let animInterval = null;

        function showNextSuggestion() {
            placeholder.textContent = suggestions[index];
            index = (index + 1) % suggestions.length;

            placeholder.style.animation = "none";
            void placeholder.offsetWidth; // Force reflow
            placeholder.style.animation = "bottom-center-top 2s ease-in-out forwards";
        }

        function startAnimation() {
            placeholder.style.display = "block";
            animInterval = setInterval(showNextSuggestion, 2200);
            showNextSuggestion();
        }

        function stopAnimation() {
            placeholder.style.display = "none";
            if (animInterval) clearInterval(animInterval);
        }

        searchInput.addEventListener("input", function () {
            if (this.value.trim().length > 0) {
                stopAnimation();
            } else {
                startAnimation();
            }
        });

        if (searchInput.value.trim().length > 0) {
            stopAnimation();
        } else {
            startAnimation();
        }
    }

    // ---------------------------------------------------------
    // 6. Real-time Counts Fetcher (Wishlist, Cart, Notifications)
    // ---------------------------------------------------------
    function fetchCounts() {
        fetch('/footer/fetch-counts')
            .then(res => {
                if (!res.ok) throw new Error('Counts endpoint not available');
                return res.json();
            })
            .then(data => {
                // Wishlist count
                const wishlistEl = document.getElementById('wishlistCount');
                if (wishlistEl) {
                    const count = data.wishlist_count ?? 0;
                    wishlistEl.textContent = count;
                    wishlistEl.style.display = count > 0 ? 'inline-block' : 'none';
                }

                // Cart count (Desktop & Mobile Bottom Nav)
                const cartEl = document.getElementById('cartCount');
                const bottomCartEl = document.getElementById('mobileBottomCartCount');
                const cartCount = data.cart_count ?? 0;

                if (cartEl) {
                    cartEl.textContent = cartCount;
                    cartEl.style.display = cartCount > 0 ? 'inline-block' : 'none';
                }
                if (bottomCartEl) {
                    bottomCartEl.textContent = cartCount;
                    bottomCartEl.style.display = cartCount > 0 ? 'inline-block' : 'none';
                }

                // Notifications count
                const notifEl = document.getElementById('notificationCount');
                if (notifEl) {
                    const count = data.notification_count ?? 0;
                    notifEl.textContent = count;
                    notifEl.style.display = count > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(() => {
                // Silent catch
            });
    }

    fetchCounts();
    setInterval(fetchCounts, 30000);

    // ---------------------------------------------------------
    // 7. Mobile Bottom Navigation Categories Trigger
    // ---------------------------------------------------------
    const bottomCatBtn = document.getElementById('mobileBottomCategoriesBtn');
    if (bottomCatBtn) {
        bottomCatBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openMobileMenu();
            if (mobileCatDropdown) {
                mobileCatDropdown.classList.add('active');
            }
        });
    }
});
</script>
