<div class="main-content" id="mainContent">
    <div class="top-bar">
        <!-- Search Form -->
        <form id="searchForm" action="search.php" method="GET" class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" 
                   class="search-input" 
                   name="q" 
                   id="searchInput"
                   placeholder="Search content, users, categories..."
                   autocomplete="off"
                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <div id="searchResults" class="search-results-dropdown" style="display:none;"></div>
        </form>

        <div class="profile-area">
            <button class="upload" onclick="openModal()">
                <i class="material-icons">&#xe2c6;</i>Upload
            </button>

            <div class="user-info" onclick="window.location.href='profile.php'">
                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                <span class="username-text">
                    <?php 
                    if (isset($_SESSION['username'])) {
                        echo htmlspecialchars($_SESSION['username']);
                    } else {
                        echo 'Profile';
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>


<style>
/* Style untuk dropdown search results */
.search-container {
    position: relative;
    display: flex;
    flex: 1;
    max-width: 500px;
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--nav-color);
    border-radius: 0 0 15px 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    margin-top: 5px;
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    border: 1px solid var(--primary-blue);
    border-top: none;
}

.search-result-item {
    padding: 12px 15px;
    border-bottom: 1px solid var(--nav-hover);
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background: var(--nav-hover);
}

.search-result-type {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 10px;
    margin-right: 10px;
    background: var(--primary-blue);
    color: white;
    text-transform: uppercase;
}

.search-result-content {
    flex: 1;
}

.search-result-title {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 3px;
    color: var(--text-color);
}

.search-result-meta {
    font-size: 12px;
    color: var(--text-color);
    opacity: 0.7;
}

.search-result-empty {
    padding: 20px;
    text-align: center;
    color: var(--text-color);
    opacity: 0.7;
}

.search-loading {
    padding: 15px;
    text-align: center;
    color: var(--text-color);
}

.search-loading i {
    margin-right: 8px;
    color: var(--primary-blue);
}

.search-view-all {
    padding: 12px;
    text-align: center;
    background: var(--primary-blue);
    color: white;
    cursor: pointer;
    border-radius: 0 0 14px 14px;
    font-weight: bold;
    transition: background 0.2s;
}

.search-view-all:hover {
    background: #2b5079;
}

.search-category {
    background: var(--minor-orange) !important;
}

.search-user {
    background: var(--minor-green) !important;
}
</style>

<script>
// Fungsi untuk search dengan autocomplete
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');

if (searchInput) {
    // Event listener untuk input
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Hide results if query is empty
        if (query.length === 0) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Show loading
        searchResults.innerHTML = `
            <div class="search-loading">
                <i class="fas fa-spinner fa-spin"></i> Searching...
            </div>
        `;
        searchResults.style.display = 'block';
        
        // Delay search to avoid too many requests
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Event listener untuk focus
    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length > 0 && searchResults.innerHTML.trim() !== '') {
            searchResults.style.display = 'block';
        }
    });
    
    // Event listener untuk submit form
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const query = searchInput.value.trim();
        if (query.length === 0) {
            e.preventDefault();
            searchInput.focus();
        }
    });
}

// Fungsi untuk melakukan search via AJAX
function performSearch(query) {
    if (query.length < 2) {
        searchResults.innerHTML = `
            <div class="search-result-empty">
                Type at least 2 characters to search
            </div>
        `;
        return;
    }
    
    // Fetch search results
    fetch(`search_ajax.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.results, query);
            } else {
                searchResults.innerHTML = `
                    <div class="search-result-empty">
                        Error: ${data.message || 'Search failed'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            searchResults.innerHTML = `
                <div class="search-result-empty">
                    Search service unavailable
                </div>
            `;
        });
}

// Fungsi untuk menampilkan hasil search
function displaySearchResults(results, query) {
    if (results.length === 0) {
        searchResults.innerHTML = `
            <div class="search-result-empty">
                No results found for "<strong>${escapeHtml(query)}</strong>"
            </div>
        `;
        return;
    }
    
    let html = '';
    
    // Batasi tampilan menjadi 5 item
    const displayResults = results.slice(0, 5);
    
    displayResults.forEach(result => {
        let typeClass = '';
        let typeText = '';
        
        if (result.type === 'content') {
            typeClass = '';
            typeText = 'CONTENT';
        } else if (result.type === 'user') {
            typeClass = 'search-user';
            typeText = 'USER';
        } else if (result.type === 'category') {
            typeClass = 'search-category';
            typeText = 'CATEGORY';
        }
        
        html += `
            <div class="search-result-item" onclick="goToSearchResult('${result.type}', ${result.id})">
                <span class="search-result-type ${typeClass}">${typeText}</span>
                <div class="search-result-content">
                    <div class="search-result-title">${escapeHtml(result.title)}</div>
                    <div class="search-result-meta">${escapeHtml(result.meta || '')}</div>
                </div>
            </div>
        `;
    });
    
    // Tambahkan "View All" link jika ada lebih dari 5 hasil
    if (results.length > 5) {
        html += `
            <div class="search-view-all" onclick="window.location.href='search.php?q=${encodeURIComponent(query)}'">
                View all ${results.length} results <i class="fas fa-arrow-right"></i>
            </div>
        `;
    }
    
    searchResults.innerHTML = html;
}

// Fungsi untuk redirect ke hasil search
function goToSearchResult(type, id) {
    if (type === 'content') {
        window.location.href = `view_content.php?id=${id}`;
    } else if (type === 'user') {
        window.location.href = `user_profile.php?id=${id}`;
    } else if (type === 'category') {
        window.location.href = `category.php?name=${encodeURIComponent(id)}`;
    }
    
    // Hide results
    searchResults.style.display = 'none';
    searchInput.value = '';
}

// Fungsi untuk escape HTML (mencegah XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Hide results ketika klik di luar
document.addEventListener('click', function(e) {
    if (!searchResults.contains(e.target) && e.target !== searchInput) {
        searchResults.style.display = 'none';
    }
});

// Navigasi dengan keyboard
searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        
        const items = searchResults.querySelectorAll('.search-result-item, .search-view-all');
        if (items.length === 0) return;
        
        let currentIndex = -1;
        
        // Cari item yang sedang aktif
        items.forEach((item, index) => {
            if (item.classList.contains('active')) {
                currentIndex = index;
                item.classList.remove('active');
            }
        });
        
        if (e.key === 'ArrowDown') {
            currentIndex = (currentIndex + 1) % items.length;
        } else if (e.key === 'ArrowUp') {
            currentIndex = (currentIndex - 1 + items.length) % items.length;
        }
        
        items[currentIndex].classList.add('active');
        items[currentIndex].scrollIntoView({ block: 'nearest' });
        
    } else if (e.key === 'Enter') {
        const activeItem = searchResults.querySelector('.active');
        if (activeItem) {
            e.preventDefault();
            activeItem.click();
        }
    }
});

// Style untuk item aktif
const style = document.createElement('style');
style.textContent = `
    .search-result-item.active,
    .search-view-all.active {
        background: var(--primary-blue) !important;
        color: white !important;
    }
    .search-result-item.active .search-result-title,
    .search-result-item.active .search-result-meta {
        color: white !important;
    }
`;
document.head.appendChild(style);
</script>