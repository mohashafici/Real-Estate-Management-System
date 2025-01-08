<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="brand-title">RealEstate</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-th-large"></i>
                My Dashboard
            </a>
        </li>
        <li class="nav-item has-submenu">
            <a class="nav-link" href="javascript:void(0);">
                <i class="fas fa-home"></i>
                Properties
            </a>
            <ul class="submenu">
                <li class="nav-item">
                    <a class="nav-link" href="add-property.php">
                        Add Property
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="properties.php?status=published">
                        Published
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="properties.php?status=pending">
                        Pending Review
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'featured-properties.php' ? 'active' : ''; ?>" href="featured-properties.php">
                <i class="fas fa-star"></i>
                Featured Properties
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'favorites.php' ? 'active' : ''; ?>" href="favorites.php">
                <i class="far fa-heart"></i>
                My Favorites
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'rental-properties.php' ? 'active' : ''; ?>" href="rental-properties.php">
                <i class="fas fa-building"></i>
                Rental Properties
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    background-color: #1a1c23;
    min-height: 100vh;
    color: #8a8b9f;
    padding: 24px 16px;
    width: 250px;
    position: fixed;
    left: 0;
    top: 0;
}
.brand-title {
    color: #fff;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 32px;
    padding: 0 16px;
}
.nav-item {
    margin: 4px 0;
}
.nav-link {
    color: #8a8b9f;
    padding: 12px 16px;
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 14px;
    display: flex;
    align-items: center;
    text-decoration: none;
}
.nav-link:hover, .nav-link.active {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.nav-link i {
    width: 20px;
    margin-right: 12px;
    font-size: 16px;
}
.submenu {
    display: none;
    list-style: none;
    padding-left: 48px;
    margin: 0;
}
.submenu .nav-link {
    padding: 8px 16px;
    font-size: 14px;
    color: #8a8b9f;
}
.submenu .nav-link:hover {
    color: #fff;
}
.has-submenu.open .submenu {
    display: block;
}
.has-submenu .nav-link::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: auto;
    transition: transform 0.2s;
}
.has-submenu.open .nav-link::after {
    transform: rotate(180deg);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submenuItems = document.querySelectorAll('.nav-item.has-submenu');
    
    submenuItems.forEach(item => {
        const link = item.querySelector('.nav-link');
        link.addEventListener('click', (e) => {
            e.preventDefault();
            submenuItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('open');
                }
            });
            item.classList.toggle('open');
        });
    });

    // Set active state based on current page and query parameters
    const currentPath = window.location.pathname;
    const searchParams = new URLSearchParams(window.location.search);
    const status = searchParams.get('status');
    
    let currentLink;
    if (status) {
        currentLink = document.querySelector(`a[href$="status=${status}"]`);
    } else {
        currentLink = document.querySelector(`a[href="${currentPath}"]`);
    }
    
    if (currentLink) {
        currentLink.classList.add('active');
        const parentSubmenu = currentLink.closest('.has-submenu');
        if (parentSubmenu) {
            parentSubmenu.classList.add('open');
        }
    }
});
</script> 