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
        const trigger = mobileCatDropdown.querySelector(':scope > a') || mobileCatDropdown.querySelector('a');
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
        const suggestions = {!! json_encode(array_values(array_map(fn($s) => "Search \"$s\"", $searchSuggestions ?? ['Fashion', 'Electronics', 'Mobiles', 'Home & Kitchen', 'Beauty', 'Sports']))) !!};

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
        const urlParams = new URLSearchParams(window.location.search);
        const modeParam = urlParams.get('mode') || '{{ session("active_shopping_mode", "shopy") }}';
        fetch(`/footer/fetch-counts?mode=${encodeURIComponent(modeParam)}`)
            .then(res => {
                if (!res.ok) throw new Error('Counts endpoint not available');
                return res.json();
            })
            .then(data => {
                // Wishlist count (store-scoped)
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

    @if(!empty($isDarkMode))
    // ---------------------------------------------------------
    // 8. User Storefront Theme Toggle (Available when Admin enables Dark Theme)
    // ---------------------------------------------------------
    try {
        const htmlEl = document.documentElement;
        const userThemeToggle = document.getElementById('userThemeToggle');
        const userThemeIcon = document.getElementById('userThemeIcon');
        const mobileThemeLabels = document.querySelectorAll('.mobileThemeLabel');
        const dropdownThemeLabels = document.querySelectorAll('.dropdownThemeLabel');

        function applyStoreTheme(theme) {
            htmlEl.setAttribute('data-theme', theme);
            if (document.body) {
                document.body.setAttribute('data-theme', theme);
            }

            if (theme === 'dark') {
                htmlEl.classList.add('dark');
                if (document.body) document.body.classList.add('dark');
                if (userThemeIcon) userThemeIcon.innerHTML = '<i class="fas fa-sun text-amber-400"></i>';
                mobileThemeLabels.forEach(el => { el.textContent = 'Dark'; });
                dropdownThemeLabels.forEach(el => { el.textContent = 'Dark'; });
            } else {
                htmlEl.classList.remove('dark');
                if (document.body) document.body.classList.remove('dark');
                if (userThemeIcon) userThemeIcon.innerHTML = '<i class="fas fa-moon text-slate-700"></i>';
                mobileThemeLabels.forEach(el => { el.textContent = 'Light'; });
                dropdownThemeLabels.forEach(el => { el.textContent = 'Light'; });
            }
        }

        function toggleThemeAction(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const current = htmlEl.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyStoreTheme(next);
            try {
                localStorage.setItem('user_theme', next);
            } catch (err) {}
        }

        // Initialize state on load
        const currentActiveTheme = htmlEl.getAttribute('data-theme') || (htmlEl.classList.contains('dark') ? 'dark' : 'light');
        applyStoreTheme(currentActiveTheme);

        // Bind all theme toggles (Desktop top-bar, mobile menu drawer, account dropdown)
        if (userThemeToggle) {
            userThemeToggle.addEventListener('click', toggleThemeAction);
        }

        document.querySelectorAll('.theme-toggle-btn, .theme-toggle-btn-mobile, .theme-toggle-btn-dropdown').forEach(btn => {
            if (btn !== userThemeToggle) {
                btn.addEventListener('click', toggleThemeAction);
            }
        });
    } catch (themeInitErr) {
        console.warn('Theme toggle initialization error:', themeInitErr);
    }
    @endif

    // ---------------------------------------------------------
    // 8. Global Wishlist Toggle (AJAX)
    // ---------------------------------------------------------
    window.toggleWishlist = function (productId, btnEl) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('{{ route("wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(async res => {
            if (res.status === 401) {
                const data = await res.json();
                if (typeof toastr !== 'undefined') {
                    toastr.info(data.message || 'Please sign in to save items to your wishlist.');
                }
                setTimeout(() => {
                    window.location.href = data.login_url || '{{ route("login") }}';
                }, 1200);
                return null;
            }
            return res.json();
        })
        .then(data => {
            if (!data || !data.success) return;

            // Update all buttons for this product on the page
            document.querySelectorAll(`button.wishlist-btn[data-product-id="${productId}"]`).forEach(btn => {
                const icon = btn.querySelector('i');
                if (data.in_wishlist) {
                    btn.classList.add('active', 'text-rose-500');
                    btn.classList.remove('text-slate-400');
                    btn.setAttribute('title', 'Remove from wishlist');
                    if (icon) icon.className = 'fa-solid fa-heart text-rose-500';
                    btn.animate([
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.3)' },
                        { transform: 'scale(1)' }
                    ], { duration: 250 });
                } else {
                    btn.classList.remove('active', 'text-rose-500');
                    btn.classList.add('text-slate-400');
                    btn.setAttribute('title', 'Add to wishlist');
                    if (icon) icon.className = 'fa-regular fa-heart';
                }
            });

            // Update modal wishlist icon if open
            const modalIcon = document.getElementById('modalWishlistIcon');
            if (modalIcon && window.currentQuickViewProductId == productId) {
                modalIcon.className = data.in_wishlist ? 'fa-solid fa-heart text-rose-500' : 'far fa-heart';
            }

            // Update top-bar wishlist count badge based on active shopping mode
            const wishlistBadge = document.getElementById('wishlistCount');
            if (wishlistBadge) {
                const urlParams = new URLSearchParams(window.location.search);
                const currentMode = urlParams.get('mode') || '{{ session("active_shopping_mode", "shopy") }}';
                
                if (currentMode === 'all') {
                    const count = data.total_count ?? data.count;
                    wishlistBadge.textContent = count;
                    wishlistBadge.style.display = count > 0 ? 'inline-block' : 'none';
                } else if (data.mode_slug && data.mode_slug === currentMode) {
                    const count = data.mode_count ?? data.count;
                    wishlistBadge.textContent = count;
                    wishlistBadge.style.display = count > 0 ? 'inline-block' : 'none';
                } else {
                    fetchCounts();
                }
            }

            // Toastr feedback
            if (typeof toastr !== 'undefined') {
                if (data.in_wishlist) {
                    toastr.success(data.message || 'Added to wishlist!');
                } else {
                    toastr.info(data.message || 'Removed from wishlist.');
                }
            }
        })
        .catch(err => {
            console.error('Wishlist toggle error:', err);
        });
    };

    // ---------------------------------------------------------
    // 9. Global Add To Cart (AJAX)
    // ---------------------------------------------------------
    window.addToCart = function (productId, quantity, btnEl, color, size) {
        quantity = quantity || 1;
        
        let originalContent = '';
        if (btnEl) {
            btnEl.disabled = true;
            originalContent = btnEl.innerHTML;
            btnEl.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Adding...';
        }

        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
                color: color || null,
                size: size || null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Could not add product to cart.');
                }
                if (btnEl) {
                    btnEl.innerHTML = originalContent;
                    btnEl.disabled = false;
                }
                return;
            }

            // Update top-bar cart count badge
            const cartBadge = document.getElementById('cartCount');
            if (cartBadge) {
                cartBadge.textContent = data.cart_count;
                cartBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }

            // Update mobile bottom nav cart count badge
            const bottomBadge = document.getElementById('mobileBottomCartCount');
            if (bottomBadge) {
                bottomBadge.textContent = data.cart_count;
                bottomBadge.style.display = data.cart_count > 0 ? 'inline-block' : 'none';
            }

            // Button feedback
            if (btnEl) {
                btnEl.innerHTML = '<i class="fa-solid fa-check text-xs"></i> Added!';
                setTimeout(() => {
                    btnEl.innerHTML = originalContent;
                    btnEl.disabled = false;
                }, 1200);
            }

            if (typeof toastr !== 'undefined') {
                toastr.success(data.message || 'Added to cart!');
            }
        })
        .catch(err => {
            console.error('Add to cart error:', err);
            if (typeof toastr !== 'undefined') {
                toastr.error('Failed to add product to cart. Please try again.');
            }
            if (btnEl) {
                btnEl.innerHTML = originalContent;
                btnEl.disabled = false;
            }
        });
    };
});
</script>
