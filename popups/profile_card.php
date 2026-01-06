
<?php
// popups/profile_card.php
?>
<div class="p-pos">
    <div class="profile-overlay">
        <div class="profile-box" id="profileCard">
            <!-- Tombol Edit di pojok kanan atas -->
            <div class="edit-btn" onclick="openProfileEdit(event)">
                <i class="fas fa-edit"></i>
                <span>edit</span>
            </div>
            
            <!-- Tombol Logout di pojok kiri atas -->
            <div class="logout-btn" onclick="window.location.href='login/logout.php'">
                <i class="fas fa-power-off"></i>
                <span>logout</span>
            </div>
        
            <div class="profile-content">
                <!-- Gambar Profil -->
                <div class="profile-image">
                    <?php if (!empty($user_data['profile_image'])): ?>
                        <?php 
                        $profile_image = strpos($user_data['profile_image'], 'uploads/profiles/') === 0 ? 
                            $user_data['profile_image'] : 'uploads/profiles/' . $user_data['profile_image'];
                        ?>
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                             alt="<?php echo htmlspecialchars($username); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                    <?php endif; ?>
                    <i class="fas fa-user-circle" 
                       style="font-size: 120px; color: var(--primary-blue); <?php echo !empty($user_data['profile_image']) ? 'display:none;' : ''; ?>"></i>
                </div>
                
                <!-- Info User -->
                <div style="text-align: center;">
                    <h2 class="username"><?php echo htmlspecialchars($username); ?></h2>
                    
                    <?php if(isset($user_data['email'])): ?>
                        <p class="details">
                            <i class="fas fa-envelope"></i> 
                            <?php echo htmlspecialchars($user_data['email']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="details">
                        <i class="fas fa-calendar-alt"></i> 
                        Member since: <?php echo $join_date; ?>
                    </p>
                    
                    <?php if(isset($user_data['bio']) && !empty($user_data['bio'])): ?>
                        <p class="details" style="font-style: italic; margin-top: 10px;">
                            "<?php echo htmlspecialchars($user_data['bio']); ?>"
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Style untuk tombol edit */
.edit-btn {
    position: absolute;
    top: 25px;
    right: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    color: var(--text-color);
    transition: transform 0.2s ease, color 0.2s ease;
    z-index: 10;
}

.edit-btn:hover {
    color: var(--primary-blue);
    transform: scale(1.1);
}

.edit-btn i {
    font-size: 1.4rem;
}

.edit-btn span {
    font-size: 10px;
    text-transform: uppercase;
    font-weight: bold;
    margin-top: 2px;
}
</style>

<!-- Include Modal Edit Profil -->
<?php include('popups/edit_profile_modal.php'); ?>

<script>
// Fungsi untuk membuka modal edit profil
function openProfileEdit(event) {
    if (event) {
        event.stopPropagation(); // Mencegah event bubbling
    }
    
    console.log('Edit button clicked');
    
    // Cek apakah modal ada
    const modal = document.getElementById('profileEditModal');
    if (!modal) {
        console.error('Modal not found!');
        return;
    }
    
    // Tampilkan modal
    modal.style.display = 'flex';
    console.log('Modal should be visible now');
    
    // Load user data
    loadUserData();
}

// Fungsi untuk memuat data user
function loadUserData() {
    console.log('Loading user data...');
    
    fetch('get_user_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('User data received:', data);
            
            if (data.success && data.user) {
                const user = data.user;
                
                // Isi form dengan data user
                document.getElementById('editUsername').value = user.username || '';
                document.getElementById('editEmail').value = user.email || '';
                document.getElementById('editBio').value = user.bio || '';
                document.getElementById('editBirthDate').value = user.birth_date || '';
                document.getElementById('editWebsite').value = user.website || '';
                
                // Tampilkan gambar profil
                const profilePreview = document.getElementById('profilePreview');
                if (user.profile_image) {
                    // Tambahkan timestamp untuk menghindari cache
                    const timestamp = new Date().getTime();
                    profilePreview.src = user.profile_image + '?t=' + timestamp;
                    profilePreview.style.display = 'block';
                } else {
                    profilePreview.style.display = 'none';
                }
                
                // Update character counters
                updateCharCounter('editUsername', 50);
                updateCharCounter('editBio', 500);
                
            } else {
                console.error('Failed to load user data:', data.message);
                alert('Failed to load user data: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading user data:', error);
            alert('Error loading user data. Please check console.');
        });
}

// Fungsi untuk update character counter
function updateCharCounter(elementId, maxLength) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const counter = element.parentElement.querySelector('.char-count');
    if (!counter) return;
    
    element.addEventListener('input', function() {
        const length = this.value.length;
        counter.textContent = length + '/' + maxLength;
        
        if (length >= maxLength) {
            counter.style.color = 'red';
            this.value = this.value.substring(0, maxLength);
        } else {
            counter.style.color = '';
        }
    });
    
    // Set initial count
    counter.textContent = element.value.length + '/' + maxLength;
}

// Fungsi untuk menutup modal
function closeProfileEdit() {
    console.log('Closing profile edit modal');
    const modal = document.getElementById('profileEditModal');
    if (modal) {
        modal.style.display = 'none';
    }
    
    // Reset form
    const form = document.getElementById('editProfileForm');
    if (form) {
        form.reset();
    }
    
    const preview = document.getElementById('profilePreview');
    if (preview) {
        preview.src = '';
        preview.style.display = 'none';
    }
}

// Debug: Log saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('Profile card loaded');
    console.log('openProfileEdit function exists:', typeof openProfileEdit === 'function');
    
    // Tes apakah button bisa diklik
    const editBtn = document.querySelector('.edit-btn');
    if (editBtn) {
        console.log('Edit button found in DOM');
        editBtn.addEventListener('click', function(e) {
            console.log('Edit button clicked via event listener');
            openProfileEdit(e);
        });
    } else {
        console.error('Edit button NOT found in DOM!');
    }
});
</script>

<div class="profile-nav">
    <a class="p-btn <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="profile.php"> Content</a>
    <a class="p-btn <?php echo ($current_page == 'activity.php') ? 'active' : ''; ?>" href="activity.php">Activity</a>
</div>

