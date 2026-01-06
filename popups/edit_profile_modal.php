<?php
// edit_profile_modal.php
?>
<form id="editProfileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
    <div class="ovr" id="profileEditModal" style="display: none;">
        <div class="popup-content">
            <div class="popup-head">
                <h2>Edit Profile</h2>
                <div class="header-icons">
                    <button type="button" class="circle" onclick="closeProfileEdit()">&times;</button>
                </div>
            </div>
            

                <div class="slider-container">
                    <div class="popup-main0">
                        <div class="popup-colu1">
                            <!-- Profile Picture -->
                            <div class="input-group">
                                <label>Profile Picture</label>
                                <div class="popup-box" style="position: relative; overflow: hidden; background-size: cover; background-position: center; cursor: pointer; height: 200px;"
                                    onclick="document.getElementById('profilePicture').click()">
                                    <div class="content-overlay" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
                                        <i class="fas fa-camera fa-3x" style="color: white; margin-bottom: 10px;"></i>
                                        <button type="button" class="buttons">Change Photo</button>
                                    </div>
                                    <img id="profilePreview" src="" style="width:100%; height:100%; object-fit:cover; display:none;">
                                    <input type="file" id="profilePicture" name="profile_picture" class="file-input" accept="image/*" style="display: none;" onchange="previewProfileImage(this)">
                                </div>
                                <p class="hint">Click to upload new profile picture (jpg, png, max 5MB)</p>
                            </div>
                            
                            <!-- Username -->
                            <div class="input-group">
                                <label>Username <span style="color:red;">*</span></label>
                                <input type="text" name="username" id="editUsername" class="search-input" required
                                    placeholder="Enter your username">
                                <span class="char-count">0/50</span>
                            </div>
                            
                            <!-- Email -->
                            <div class="input-group">
                                <label>Email <span style="color:red;">*</span></label>
                                <input type="email" name="email" id="editEmail" class="search-input" required
                                    placeholder="Enter your email">
                            </div>
                            
                            <!-- Bio/Description -->
                            <div class="input-group">
                                <label>Bio</label>
                                <textarea name="bio" id="editBio" class="search-input" rows="4" 
                                        placeholder="Tell us about yourself..."></textarea>
                                <span class="char-count">0/500</span>
                            </div>
                            
                            <!-- Birth Date -->
                            <div class="input-group">
                                <label>Birth Date</label>
                                <input type="date" name="birth_date" id="editBirthDate" class="search-input">
                            </div>
                            
                            <!-- Website/Link -->
                            <div class="input-group">
                                <label>Website</label>
                                <input type="url" name="website" id="editWebsite" class="search-input" 
                                    placeholder="https://example.com">
                            </div>
                            
                            <!-- Current Password (untuk verifikasi) -->
                            <div class="input-group">
                                <label>Current Password (required for changes)</label>
                                <input type="password" name="current_password" id="currentPassword" class="search-input" 
                                    placeholder="Enter current password to confirm changes" required>
                            </div>
                            
                            <!-- New Password (optional) -->
                            <div class="input-group">
                                <label>New Password (optional)</label>
                                <input type="password" name="new_password" id="newPassword" class="search-input" 
                                    placeholder="Enter new password">
                            </div>
                            
                            <!-- Confirm New Password -->
                            <div class="input-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirmPassword" class="search-input" 
                                    placeholder="Confirm new password">
                            </div>
                            
                            <div class="error-msg" id="profileError" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            
            
            <div class="popup-footer">
                <button type="button" class="buttons" onclick="closeProfileEdit()">Cancel</button>
                <button type="submit" class="buttons" style="background: var(--minor-green);">Save Changes</button>
            </div>
        </div>
    </div>
</form>
<script>
// Fungsi preview gambar profil
function previewProfileImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        const preview = document.getElementById('profilePreview');
        
        // Validasi ukuran file (max 5MB)
        if (input.files[0].size > 5 * 1024 * 1024) {
            alert('File is too large! Maximum size is 5MB.');
            input.value = '';
            return;
        }
        
        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(input.files[0].type)) {
            alert('Invalid file type. Please upload JPEG, PNG or GIF image.');
            input.value = '';
            return;
        }
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            preview.parentElement.querySelector('.content-overlay').style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Fungsi validasi form
function validateProfileForm() {
    const username = document.getElementById('editUsername').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('profileError');
    
    // Reset error
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    
    // Validasi username
    if (!username) {
        showError('Username is required');
        return false;
    }
    
    if (username.length < 3 || username.length > 50) {
        showError('Username must be between 3 and 50 characters');
        return false;
    }
    
    // Validasi email
    if (!email) {
        showError('Email is required');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('Please enter a valid email address');
        return false;
    }
    
    // Validasi password saat ini
    if (!currentPassword) {
        showError('Current password is required to confirm changes');
        return false;
    }
    
    // Validasi password baru (jika diisi)
    if (newPassword) {
        if (newPassword.length < 6) {
            showError('New password must be at least 6 characters long');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            showError('New passwords do not match');
            return false;
        }
    }
    
    return true;
}

function showError(message) {
    const errorDiv = document.getElementById('profileError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Handle form submission dengan AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editProfileForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateProfileForm()) {
                return;
            }
            
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
                    
                    // Tutup modal
                    closeProfileEdit();
                    
                    // Refresh halaman setelah 1 detik
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    // Tampilkan error
                    const errorDiv = document.getElementById('profileError');
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                    errorDiv.scrollIntoView({ behavior: 'smooth' });
                    
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
    }
});

// Close modal dengan ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('profileEditModal');
        if (modal && modal.style.display === 'flex') {
            closeProfileEdit();
        }
    }
});

// Close modal saat klik di luar
document.addEventListener('click', function(e) {
    const modal = document.getElementById('profileEditModal');
    const modalContent = document.querySelector('#profileEditModal .popup-content');
    
    if (modal && modal.style.display === 'flex' && modalContent && !modalContent.contains(e.target)) {
        closeProfileEdit();
    }
});
</script>