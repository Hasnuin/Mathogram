const settingsBtn = document.getElementById('settings-btn');
const settingsPopup = document.getElementById('settings-popup');
const closePopup = document.getElementById('close-popup');
const themeSelect = document.getElementById('theme-select');

// 1. Fungsi Buka/Tutup Popup
settingsBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    settingsPopup.classList.toggle('show');
});

closePopup.addEventListener('click', () => {
    settingsPopup.classList.remove('show');
});

// Menutup popup jika klik di luar area popup
document.addEventListener('click', (e) => {
    if (!settingsPopup.contains(e.target) && e.target !== settingsBtn) {
        settingsPopup.classList.remove('show');
    }
});

// 2. Logika Dropdown Tema (DIUBAH KE documentElement)
themeSelect.addEventListener('change', (e) => {
    const selectedTheme = e.target.value;
    
    if (selectedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode'); // Target html
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark-mode'); // Target html
        localStorage.setItem('theme', 'light');
    }
});

// 3. Cek Simpanan Tema saat Halaman Dimuat (DIUBAH KE documentElement)
window.addEventListener('load', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode'); // Target html
        themeSelect.value = 'dark';
    } else {
        themeSelect.value = 'light';
    }
    
    // Aktifkan transisi setelah tema terpasang agar tidak flash saat load
    document.body.classList.add('preload-transitions');
});

let currentIndex = 0;

function openPost(){
    document.getElementById("ovrPost").style.display = "flex";
}
function closePost(){
    document.getElementById("ovrPost").style.display = "none";
}
function openStat(){
    document.getElementById("ovrStat").style.display = "flex";
}
function closeStat(){
    document.getElementById("ovrStat").style.display = "none";
    window.location.href='activity.html'
}

function openModal() {
  document.getElementById("overlay").style.display = "flex";
  currentIndex = 0;
  updateUI();
  updateButtonVisibility();
}

function upModal(){
    // Submit form secara manual
    document.getElementById('uploadForm').submit();
}

function noupModal(){
    document.getElementById("success").style.display="none";
}

function closeModal() {
  document.getElementById("overlay").style.display = "none";
  currentIndex = 0;
  updateUI();
}

function updateButtonVisibility() {
  const backBtn = document.getElementById('back');
  const nextBtn = document.getElementById('next');
  const upBtn = document.getElementById('upload');
  if (currentIndex === 0) {
        backBtn.style.visibility = 'hidden';
        nextBtn.style.display="flex"
        upBtn.style.display="none"; 

  } else if (currentIndex === 2) {
        upBtn.style.display="flex";
        nextBtn.style.display="none"
  } else{
        backBtn.style.visibility = 'visible';
        nextBtn.style.display="flex"
        upBtn.style.display="none";
  }
}

function updateUI() {
  const track = document.querySelector('.slider-track');
  track.style.transform = 'translateX(0%)';
}

function moveSlide(direction) {
  const track = document.querySelector('.slider-track');
  const slides = document.querySelectorAll('.slide');
  const totalSlides = slides.length;

  currentIndex += direction;

  // JIKA BERPINDAH KE SLIDE PREVIEW (Index 2), UPDATE DATA
  if (currentIndex === 2) {
      updatePreview();
  }

  if (currentIndex >= totalSlides) {
    currentIndex = 0;
  } else if (currentIndex < 0) {
    currentIndex = totalSlides - 1;
  }

  updateButtonVisibility();
  const offset = -currentIndex * 100;
  track.style.transform = `translateX(${offset}%)`;
}

document.addEventListener('DOMContentLoaded', () => {
    // Mengambil elemen yang dibutuhkan
    const bookmarkTrigger = document.getElementById('bookmarkTrigger');
    const bookmarkIcon = document.getElementById('bookmarkIcon');
    const bookmarkCount = document.getElementById('bookmarkCount');

    // State awal (apakah sudah dibookmark atau belum)
    let isBookmarked = false;

    bookmarkTrigger.addEventListener('click', () => {
        // Toggle state
        isBookmarked = !isBookmarked;

        if (isBookmarked) {
            // Aksi: Bookmark Aktif
            
            // 1. Ubah class wrapper agar warna teks/icon jadi kuning (via CSS)
            bookmarkTrigger.classList.add('active');

            // 2. Ubah icon font-awesome dari regular (garis) ke solid (isi penuh)
            bookmarkIcon.classList.remove('fa-regular');
            bookmarkIcon.classList.add('fa-solid');

            // 3. Tambah angka
            let currentCount = parseInt(bookmarkCount.innerText);
            bookmarkCount.innerText = currentCount + 1;

        } else {
            // Aksi: Bookmark Non-aktif (opsional, jika ingin bisa di-unclick)
            
            // 1. Hapus class active
            bookmarkTrigger.classList.remove('active');

            // 2. Kembalikan icon ke regular
            bookmarkIcon.classList.remove('fa-solid');
            bookmarkIcon.classList.add('fa-regular');

            // 3. Kurangi angka kembali
            let currentCount = parseInt(bookmarkCount.innerText);
            // Mencegah angka negatif
            if(currentCount > 0) {
                bookmarkCount.innerText = currentCount - 1;
            }
        }
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const charts = document.querySelectorAll('.pie-chart');

    charts.forEach(chart => {
        // Ambil target persentase dari style inline
        const targetValue = chart.style.getPropertyValue('--p');
        let startValue = 0;
        const duration = 1500; // 1.5 detik
        const stepTime = Math.abs(Math.floor(duration / targetValue));

        // Animasi angka dan lingkaran
        const timer = setInterval(() => {
            startValue++;
            chart.style.setProperty('--p', startValue);
            
            // Jika diagram memiliki angka di dalamnya, update juga teksnya (opsional)
            // chart.querySelector('.main-number').innerText = startValue;

            if (startValue >= targetValue) {
                clearInterval(timer);
            }
        }, stepTime);
    });
});

// --- FUNGSI DINAMIS QUIZ ---

// 1. Fungsi menambah jawaban baru dalam satu pertanyaan
function addAnswer(buttonElement) {
    // Cari kontainer jawaban terdekat dari tombol + yang diklik
    const questionCard = buttonElement.closest('.question-card');
    const answersList = questionCard.querySelector('.answers-list');
    
    // Dapatkan indeks pertanyaan dari data-question (1, 2, 3, ...)
    const questionNum = parseInt(questionCard.getAttribute('data-question'));
    // Konversi ke indeks array (0, 1, 2, ...)
    const questionIndex = questionNum - 1;
    
    // Hitung jumlah jawaban yang sudah ada
    const answerCount = answersList.querySelectorAll('.search-container.ans').length;

    // Buat elemen jawaban baru dengan struktur yang sesuai
    const newAnswerHtml = `
        <div class="search-container ans">
            <label class="checkbox-container">
                <input type="radio" name="correct_answer[${questionIndex}]" value="${answerCount}">
                <span class="checkmark"></span>
            </label>
            <input type="text" name="answers[${questionIndex}][]" class="search-input" placeholder="Input answer">
            <button type="button" class="remove-ans" onclick="this.parentElement.remove(); reorderAnswers(this);" style="background:none; border:none; color:red; cursor:pointer; margin-left:5px;">&times;</button>
        </div>
    `;

    answersList.insertAdjacentHTML('beforeend', newAnswerHtml);
}

// 2. Fungsi menambah pertanyaan baru
function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionCount = container.querySelectorAll('.question-card').length;
    const questionIndex = questionCount; // Index untuk array (0, 1, 2, ...)

    const newQuestionHtml = `
        <div class="popup-main list-card question-card" data-question="${questionCount + 1}" style="margin-top: 20px; border-top: 1px solid #444; padding-top: 20px;">
            <div class="input-group">
                <label class="question-label">Question ${questionCount + 1}</label>
                <div class="popup-colu1">
                    <div class="popup-box" style="position: relative; overflow: hidden; background-size: cover; background-position: center;">
                        <div class="content-overlay">
                            <button type="button" class="buttons" onclick="triggerUpload(this)">Upload File</button>
                        </div>
                        <input type="file" name="question_img[]" class="file-input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    </div>
                </div>
                <textarea name="question_text[]" placeholder="Enter your question here..." rows="4"></textarea>
            </div>

            <div class="input-group">
                <label>Answers (Select correct answer)</label>
                <div class="answers-list">
                    <!-- Answer 1 -->
                    <div class="search-container ans">
                        <label class="checkbox-container">
                            <input type="radio" name="correct_answer[${questionIndex}]" value="0" checked>
                            <span class="checkmark"></span>
                        </label>
                        <input type="text" name="answers[${questionIndex}][]" class="search-input" placeholder="Input answer">
                        <button type="button" class="remove-ans" onclick="this.parentElement.remove(); reorderAnswers(this);" style="background:none; border:none; color:red; cursor:pointer; margin-left:5px;">&times;</button>
                    </div>
                    <!-- Answer 2 -->
                    <div class="search-container ans">
                        <label class="checkbox-container">
                            <input type="radio" name="correct_answer[${questionIndex}]" value="1">
                            <span class="checkmark"></span>
                        </label>
                        <input type="text" name="answers[${questionIndex}][]" class="search-input" placeholder="Input answer">
                        <button type="button" class="remove-ans" onclick="this.parentElement.remove(); reorderAnswers(this);" style="background:none; border:none; color:red; cursor:pointer; margin-left:5px;">&times;</button>
                    </div>
                </div>
                <div class="popup-main0 list-card add-answer-btn" onclick="addAnswer(this)">
                    <div class="text-Mid">+ Add Answer</div>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove(); reorderQuestions();" style="color: #ff4d4d; background: none; border: none; cursor: pointer; font-size: 0.8rem; margin-top: 10px;">Remove Question</button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', newQuestionHtml);
}

// 3. Fungsi untuk mengurutkan ulang radio button value ketika jawaban dihapus
function reorderAnswers(removedElement) {
    const questionCard = removedElement.closest('.question-card');
    const answersList = questionCard.querySelector('.answers-list');
    const answers = answersList.querySelectorAll('.search-container.ans');
    
    // Update value radio button sesuai dengan urutan baru
    answers.forEach((answer, index) => {
        const radioInput = answer.querySelector('input[type="radio"]');
        radioInput.value = index;
    });
}

// 4. Fungsi pembantu untuk mengurutkan kembali nomor pertanyaan jika ada yang dihapus
function reorderQuestions() {
    const questions = document.querySelectorAll('.question-card');
    
    questions.forEach((card, index) => {
        const newNum = index + 1;
        card.setAttribute('data-question', newNum);
        card.querySelector('.question-label').innerText = `Question ${newNum}`;
        
        // Update index array untuk input radio dan text
        const questionIndex = newNum - 1;
        const radioInputs = card.querySelectorAll('input[type="radio"]');
        const textInputs = card.querySelectorAll('input[type="text"]');
        
        // Update radio buttons
        radioInputs.forEach(radio => {
            const oldName = radio.getAttribute('name');
            radio.setAttribute('name', `correct_answer[${questionIndex}]`);
        });
        
        // Update text inputs untuk jawaban
        textInputs.forEach(textInput => {
            textInput.setAttribute('name', `answers[${questionIndex}][]`);
        });
    });
}

// Fungsi untuk membatasi karakter dan memperbarui UI
function handleCharLimit(textarea, limit) {
    const countSpan = textarea.parentElement.querySelector('.char-count');
    
    textarea.addEventListener('input', () => {
        // Potong teks jika melebihi limit
        if (textarea.value.length > limit) {
            textarea.value = textarea.value.substring(0, limit);
        }
        
        // Update angka di UI
        const currentLength = textarea.value.length;
        if (countSpan) {
            countSpan.innerText = `${currentLength}/${limit}`;
            
            // Opsional: Beri warna merah jika sudah mencapai limit
            if (currentLength >= limit) {
                countSpan.style.color = "red";
            } else {
                countSpan.style.color = "";
            }
        }
    });
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    // Cari semua textarea yang memiliki saudara .char-count
    const textareas = document.querySelectorAll('.input-group textarea');
    
    textareas.forEach(txt => {
        const countSpan = txt.parentElement.querySelector('.char-count');
        if (countSpan) {
            // Ambil limit dari teks yang ada (misal "0/100" diambil 100-nya)
            const limit = parseInt(countSpan.innerText.split('/')[1]);
            handleCharLimit(txt, limit);
        }
    });
});

// 1. Fungsi untuk memicu klik pada input file yang tersembunyi
function triggerUpload(button) {
    const parent = button.closest('.popup-box');
    const fileInput = parent.querySelector('.file-input');
    fileInput.click();
}

// 2. Fungsi untuk membaca file dan mengganti background
function previewImage(input) {
    const file = input.files[0];
    const MAX_SIZE = 100 * 1024 * 1024;
    const parentBox = input.closest('.popup-box');
    const contentOverlay = parentBox.querySelector('.content-overlay');

    if (file && file.size > MAX_SIZE) {
        alert("File terlalu besar! Maksimal 100MB.");
        input.value = ""; // Reset input
        return;
    }
    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            // Mengatur background image pada popup-box
            parentBox.style.backgroundImage = `url('${e.target.result}')`;
            
            // Opsional: Membuat konten tombol/icon menjadi semi-transparan 
            // agar gambar terlihat jelas tapi tombol tetap bisa diklik untuk ganti file
            contentOverlay.style.opacity = "0.3";
            contentOverlay.style.backgroundColor = "rgba(0,0,0,0.2)";
        }

        reader.readAsDataURL(file);
    }
}

function updatePreview() {
    // 1. Ambil data dari Slide 1
    const titleInput = document.getElementById('input-title').value;
    const descInput = document.getElementById('input-desc').value;
    const categoryInput = document.getElementById('input-category').value;
    const thumbnailImg = document.querySelector('#up-detail .file-input[name="thumbnail"]');

    // 2. Tempelkan ke Slide 3
    document.getElementById('preview-title').innerText = titleInput || "No Title Provided";
    document.getElementById('preview-category-tag').innerText = categoryInput || "No Category";
    
    // Format deskripsi agar baris baru terbaca
    document.getElementById('preview-desc').innerHTML = descInput.replace(/\n/g, '<br>') || "No Description";

    // 3. Update Thumbnail Preview
    if (thumbnailImg.files && thumbnailImg.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-thumbnail').src = e.target.result;
        };
        reader.readAsDataURL(thumbnailImg.files[0]);
    }
}

// Fungsi validasi form sebelum submit
function validateForm() {
    const title = document.getElementById('input-title').value.trim();
    const category = document.getElementById('input-category').value;
    const mainFile = document.querySelector('input[name="main_file"]').files[0];
    
    // Validasi judul
    if (!title) {
        alert('Please enter a title');
        moveSlide(-2); // Kembali ke slide 1
        return false;
    }
    
    // Validasi kategori (jika kolom category ada di database)
    if (!category) {
        alert('Please select a category');
        moveSlide(-2); // Kembali ke slide 1
        return false;
    }
    
    // Validasi file utama
    if (!mainFile) {
        alert('Please upload a main file');
        moveSlide(-2); // Kembali ke slide 1
        return false;
    }
    
    // Validasi ukuran file (100MB)
    const maxSize = 100 * 1024 * 1024; // 100MB
    if (mainFile.size > maxSize) {
        alert('File is too large! Maximum size is 100MB');
        return false;
    }
    
    // Validasi tipe file
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
    if (!allowedTypes.includes(mainFile.type)) {
        alert('Invalid file type. Please upload an image (JPEG, PNG, GIF) or video (MP4, WebM)');
        return false;
    }
    
    // Tampilkan loading
    document.getElementById('upload').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    document.getElementById('upload').disabled = true;
    
    return true; // Lanjutkan submit
}

// Fungsi untuk preview kategori
function updatePreview() {
    const titleInput = document.getElementById('input-title').value;
    const descInput = document.getElementById('input-desc').value;
    const categoryInput = document.getElementById('input-category').value;
    const thumbnailImg = document.querySelector('#up-detail .file-input[name="thumbnail"]');

    document.getElementById('preview-title').innerText = titleInput || "No Title Provided";
    document.getElementById('preview-category-tag').innerText = categoryInput || "No Category";
    document.getElementById('preview-desc').innerHTML = descInput.replace(/\n/g, '<br>') || "No Description";

    if (thumbnailImg.files && thumbnailImg.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-thumbnail').src = e.target.result;
        };
        reader.readAsDataURL(thumbnailImg.files[0]);
    }
}

// --- FUNGSI UNTUK LOAD CONTENT MODAL ---

// Fungsi untuk memuat data ke modal
function loadContentModal(contentData) {
    console.log('Loading content:', contentData);
    
    // Tampilkan modal
    const ovrPost = document.getElementById('ovrPost');
    const modal = document.getElementById('contentModal');
    
    // Set data attribute untuk akses nanti
    modal.dataset.contentId = contentData.id;
    modal.dataset.filePath = contentData.file_path;
    
    // Update thumbnail/image
    const thumbnailImg = document.getElementById('contentThumbnail');
    const videoPlayer = document.getElementById('contentVideo');
    
    // Tentukan apakah ini video atau image
    if (contentData.file_type === 'video') {
        // Tampilkan video player
        thumbnailImg.style.display = 'none';
        videoPlayer.style.display = 'block';
        videoPlayer.src = contentData.file_path;
        videoPlayer.poster = contentData.thumbnail || '';
    } else {
        // Tampilkan thumbnail
        thumbnailImg.style.display = 'block';
        videoPlayer.style.display = 'none';
        
        // Set thumbnail dengan fallback
        let thumbPath = contentData.thumbnail;
        if (thumbPath && !thumbPath.includes('uploads/thumbnails/')) {
            thumbPath = 'uploads/thumbnails/' + thumbPath;
        }
        thumbnailImg.src = thumbPath || 'uploads/thumbnails/default.jpg';
        thumbnailImg.onerror = function() {
            this.src = 'https://via.placeholder.com/800x450/326090/ffffff?text=' + encodeURIComponent(contentData.title.substring(0, 30));
        };
    }
    
    // Update title
    document.getElementById('contentTitle').textContent = contentData.title;
    document.getElementById('modalTitle').textContent = contentData.title;
    
    // Update author
    document.getElementById('contentAuthor').textContent = contentData.username;
    
    // Update category
    document.getElementById('contentCategory').textContent = contentData.category;
    
    // Update description (dengan line breaks)
    const descElement = document.getElementById('contentDescription');
    if (contentData.description) {
        descElement.innerHTML = contentData.description.replace(/\n/g, '<br>');
    } else {
        descElement.textContent = 'No description available.';
    }
    
    // Update file info
    document.getElementById('contentFileType').textContent = contentData.file_type;
    
    // Format date
    if (contentData.created_at) {
        const date = new Date(contentData.created_at);
        document.getElementById('contentDate').textContent = date.toLocaleDateString();
    }
    
    // Tampilkan modal
    ovrPost.style.display = "flex";
    
    // Reset slide ke konten utama
    showContentMain();
}

// Fungsi untuk menutup modal (sudah ada)
function closePost() {
    document.getElementById("ovrPost").style.display = "none";
    // Reset video jika sedang diputar
    const videoPlayer = document.getElementById('contentVideo');
    if (videoPlayer) {
        videoPlayer.pause();
        videoPlayer.currentTime = 0;
    }
}

// Event listener untuk close modal dengan ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePost();
    }
});

// Event listener untuk klik di luar modal
document.addEventListener('click', function(e) {
    const ovrPost = document.getElementById('ovrPost');
    const modal = document.getElementById('contentModal');
    
    if (ovrPost.style.display === 'flex' && 
        !modal.contains(e.target) && 
        !e.target.closest('.post')) {
        closePost();
    }
});

// --- FUNGSI UNTUK EDIT PROFILE FORM ---

// Handle form submission dengan AJAX
document.getElementById('editProfileForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Tampilkan loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tampilkan pesan sukses
            alert(data.message);
            
            // Update session data jika diperlukan
            if (data.profile_image) {
                const profileImage = document.querySelector('.profile-image img');
                if (profileImage) {
                    profileImage.src = data.profile_image + '?t=' + new Date().getTime();
                    profileImage.style.display = 'block';
                    profileImage.nextElementSibling.style.display = 'none';
                }
            }
            
            // Refresh halaman setelah 1 detik
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } else {
            // Tampilkan error
            const errorDiv = document.getElementById('profileError');
            errorDiv.textContent = data.message;
            errorDiv.style.display = 'block';
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Fungsi untuk validasi URL
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Fungsi untuk format tanggal
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}


// Di script.js, tambahkan fungsi search
// (Fungsi-fungsi search utama sudah ada di topbar.php)
// Berikut fungsi tambahan jika diperlukan:

// Global search function (bisa dipanggil dari mana saja)
function globalSearch(query) {
    if (!query || query.trim().length === 0) return;
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = query;
        searchInput.focus();
        
        // Trigger input event untuk search
        const event = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(event);
    } else {
        // Fallback: redirect ke search page
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
    }
}

// Keyboard shortcut untuk search
document.addEventListener('keydown', function(e) {
    // Ctrl+K atau Cmd+K untuk focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Escape untuk clear search
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && document.activeElement === searchInput) {
            searchInput.blur();
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.style.display = 'none';
            }
        }
    }
});