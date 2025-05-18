document.addEventListener("DOMContentLoaded", () => {
    // 🔔 Flash-повідомлення (оновлене: зверху сторінки, не зсуває вміст)
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        message.style.position = 'fixed';
        message.style.top = '20px';
        message.style.left = '50%';
        message.style.transform = 'translateX(-50%)';
        message.style.zIndex = '9999';
        message.style.maxWidth = '90%';
        message.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';

        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });

    // 📁 Вкладки (AJAX-завантаження)
    const menuItems = document.querySelectorAll(".menu__item");
    const tabContent = document.getElementById("tabContent");

    const loadTab = (tabId) => {
        fetch(`tabs/${tabId}.php`)
            .then(res => res.text())
            .then(html => {
                tabContent.innerHTML = html;
                history.replaceState(null, null, `#${tabId}`);
                setActiveMenu(tabId);

                if (tabId === 'clients') initSearch('clientSearchInput', 'clientSearchBtn', 'clientsTable');
                if (tabId === 'orders') initSearch('orderSearchInput', 'orderSearchBtn', 'ordersMainTable');
                if (tabId === 'inventory') initSearch('inventorySearchInput', 'inventorySearchBtn', 'inventoryTable');
                if (tabId === 'reports') initSearch('ordersSearchInput', 'ordersSearchBtn', 'ordersTable');
                if (tabId === 'users') initSearch('userSearchInput', 'userSearchBtn', 'usersTable');
            })
            .catch(() => {
                tabContent.innerHTML = "<p>⚠️ Помилка завантаження вкладки.</p>";
            });
    };

    const setActiveMenu = (tabId) => {
        menuItems.forEach(item => {
            const parent = item.parentElement;
            item.classList.remove("menu__item--active");
            parent.style.transition = "background-color 0.3s ease";
            if (item.getAttribute("href") === `#${tabId}`) {
                item.classList.add("menu__item--active");
                parent.style.backgroundColor = "#495057";
                item.style.textDecoration = "none";
            } else {
                parent.style.backgroundColor = "transparent";
                item.style.textDecoration = "none";
            }
        });
    };

    const initialTab = window.location.hash.substring(1) || 'clients';
    loadTab(initialTab);

    menuItems.forEach(item => {
        item.addEventListener("click", (e) => {
            e.preventDefault();
            const targetTab = item.getAttribute("href").substring(1);
            if (targetTab === 'logout') {
                const confirmed = confirm('❓ Ви справді хочете вийти з системи?');
                if (confirmed) {
                    window.location.href = '/masterretail/logout.php';
                }
                return;
            }
            loadTab(targetTab);
        });
    });
});

function initSearch(inputId, buttonId, tableId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    const table = document.getElementById(tableId);

    function filterRows() {
        const value = input.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    }

    if (input && button && table) {
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') filterRows();
        });
        button.addEventListener('click', filterRows);
    }
}
