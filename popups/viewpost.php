<?php
// view_content_modal.php
// Modal yang akan diisi dengan data konten secara dinamis
?>
<div class="ovr-post" id="ovrPost">
    <div id="contentModal" class="popup-content">
        <div class="popup-head">
            <h2 id="modalTitle">Content Details</h2>
            <div class="header-icons">
                <a class="clean-link" href="javascript:void(0)" onclick="showContentInfo()">ⓘ</a>
                <button class="circle" onclick="closePost()">&times;</button>
            </div>
        </div>
        <div class="slider-container">
            <div class="slider-track">
                <div class="slide" id="contentSlide">
                    <div class="popup-main0">
                        <div class="popup-colu1">
                            <!-- Thumbnail/Media Area -->
                            <div class="hero-banner" id="contentMedia">
                                <img id="contentThumbnail" src="" alt="Content thumbnail">
                                <!-- Video Player (jika tipe video) -->
                                <video id="contentVideo" controls style="display:none; width:100%; height:100%; object-fit:contain;">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            
                            <!-- Title & Actions -->
                            <div class="footer-row">
                                <h3 id="contentTitle">Loading title...</h3>
                                <div class="bookmark-wrapper" id="bookmarkTrigger" onclick="toggleBookmark(event)">
                                    <i class="fa-regular fa-bookmark" id="bookmarkIcon"></i>
                                </div>
                            </div>
                            
                            <!-- Author Info -->
                            <div class="user-info" style="margin: 10px 0;">
                                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                <span class="username-text" id="contentAuthor">Loading author...</span>
                                <span class="buttons" id="contentCategory" style="margin-left:10px;">Category</span>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <h4>Description</h4>
                                <p id="contentDescription">
                                    Loading description...
                                </p>
                            </div>
                            
                            <!-- File Info -->
                            <div id="fileInfo" style="margin-top:20px; padding:10px; background:var(--nav-color); border-radius:10px;">
                                <small>
                                    <strong>File Type:</strong> <span id="contentFileType">Unknown</span><br>
                                    <strong>Uploaded:</strong> <span id="contentDate">Unknown date</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Slide untuk info tambahan (opsional) -->
                <div class="slide" id="infoSlide" style="display:none;">
                    <div class="popup-main0">
                        <div class="popup-colu1">
                            <h3>Content Information</h3>
                            <div id="contentStats">
                                <p>More details about this content...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="popup-footer">
            <button class="buttons" id="startQuizBtn" onclick="startQuiz()">Start Quiz</button>
        </div>
    </div>
</div>

<script>

    
// Fungsi untuk menampilkan info tambahan
function showContentInfo() {
    const contentSlide = document.getElementById('contentSlide');
    const infoSlide = document.getElementById('infoSlide');
    
    contentSlide.style.display = 'none';
    infoSlide.style.display = 'block';
    
    // Update button text
    document.querySelector('.popup-footer').innerHTML = `
        <button class="buttons" onclick="showContentMain()">Back to Content</button>
    `;
}

// Fungsi kembali ke konten utama
function showContentMain() {
    const contentSlide = document.getElementById('contentSlide');
    const infoSlide = document.getElementById('infoSlide');
    
    contentSlide.style.display = 'block';
    infoSlide.style.display = 'none';
    
    // Kembalikan button asli
    document.querySelector('.popup-footer').innerHTML = `
        <button class="buttons" id="startQuizBtn" onclick="startQuiz()">Start Quiz</button>
        <button class="buttons" id="viewFileBtn" onclick="viewOriginalFile()">View File</button>
    `;
}

// Fungsi untuk memulai quiz
// Fungsi untuk memulai quiz
function startQuiz() {
    const contentId = document.getElementById('contentModal').dataset.contentId;
    if (contentId) {
        window.location.href = 'quiz.php?content_id=' + contentId;
    } else {
        alert('Quiz not available for this content');
    }
}

// Fungsi untuk melihat file asli
function viewOriginalFile() {
    const filePath = document.getElementById('contentModal').dataset.filePath;
    if (filePath) {
        window.open(filePath, '_blank');
    } else {
        alert('File not available');
    }
}
function toggleBookmark(event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    const contentId = document.getElementById('contentModal').dataset.contentId;
    const bookmarkTrigger = document.getElementById('bookmarkTrigger');
    const bookmarkIcon = document.getElementById('bookmarkIcon');
    const bookmarkCount = document.getElementById('bookmarkCount');
    
    const isBookmarked = bookmarkTrigger.classList.contains('active');
    
    fetch('bookmark_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: isBookmarked ? 'remove' : 'add',
            content_id: contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isBookmarked) {
                // Unbookmark
                bookmarkTrigger.classList.remove('active');
                bookmarkIcon.classList.remove('fa-solid');
                bookmarkIcon.classList.add('fa-regular');
                
                let currentCount = parseInt(bookmarkCount.textContent);
                if (currentCount > 0) {
                    bookmarkCount.textContent = currentCount - 1;
                }
            } else {
                // Bookmark
                bookmarkTrigger.classList.add('active');
                bookmarkIcon.classList.remove('fa-regular');
                bookmarkIcon.classList.add('fa-solid');
                
                let currentCount = parseInt(bookmarkCount.textContent);
                bookmarkCount.textContent = currentCount + 1;
            }
            
            // Update bookmark ID jika perlu
            if (data.bookmark_id) {
                bookmarkTrigger.dataset.bookmarkId = data.bookmark_id;
            }
        } else {
            alert(data.message || 'Bookmark action failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Load bookmark status saat modal dibuka
function loadBookmarkStatus(contentId) {
    fetch('bookmark_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'check',
            content_id: contentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const bookmarkTrigger = document.getElementById('bookmarkTrigger');
            const bookmarkIcon = document.getElementById('bookmarkIcon');
            
            if (data.is_bookmarked) {
                bookmarkTrigger.classList.add('active');
                bookmarkIcon.classList.remove('fa-regular');
                bookmarkIcon.classList.add('fa-solid');
                bookmarkTrigger.dataset.bookmarkId = data.bookmark_id;
            }
        }
    })
    .catch(error => {
        console.error('Error loading bookmark status:', error);
    });
}

// Fungsi untuk menutup modal (sudah ada di script.js)
// function closePost() {
//     document.getElementById("ovrPost").style.display = "none";
// }
</script>