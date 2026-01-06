<!-- Modal Upload -->
<form id="uploadForm" action="save_uploads.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
    <div class="ovr" id="overlay">
            <div id="up-popup" class="popup-content">
                <div class="popup-head">
                    <h2>Upload Content</h2>
                    <div class="header-icons">
                        <a class="clean-link" href="">ⓘ</a>
                        <button type="button" class="circle" onclick="closeModal()">&times;</button>
                    </div>
                </div>
                <div class="slider-container">
                    <div class="slider-track">
                        <!-- Slide 1: Detail -->
                        <div class="slide" id="up-detail">
                            <div class="sm-main">
                                <div class="popup-steps">
                                    <div class="sm-rec"></div>
                                    <div class="sm-div">
                                        <div class="sm-circle active">1</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle">2</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle">3</div>
                                    </div>
                                </div>
                            </div>
                
                
                            <div class="">
                                <h3 class="popup-title">Detail</h3>
                                <div class="popup-main">
                                    <div class="popup-colu1">
                                        <div class="input-group">
                                            <label>Title (required)</label>
                                            <textarea name="title" id="input-title" placeholder="add a title that explain your content" rows="2" required></textarea>
                                            <span class="char-count">0/100</span>
                                        </div>
                                        <div class="input-group">
                                            <label>Description</label>
                                            <textarea name="description" id="input-desc" placeholder="tell people whats your content about..." rows="4"></textarea>
                                        </div>
                                        <div class="input-group">
                                            <label>Thumbnail</label>
                                            <div class="popup-box" style="position: relative; overflow: hidden; background-size: cover; background-position: center;">
                                                <div class="content-overlay">
                                                    <button type="button" class="buttons" onclick="triggerUpload(this)">Upload File</button>
                                                </div>
                                                <input type="file" name="thumbnail" class="file-input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                            </div>
                                            <p class="hint">Choose File from your device (png, or jpg)</p>
                                        </div>
                                    </div>
                
                                    <div class="popup-colu1">
                                        <label>Category (required)</label>
                                        <select name="category" id="input-category" class="search-input" required>
                                            <option value="" disabled selected>Select a category</option>
                                            <option value="Basic Math">Basic Math</option>
                                            <option value="Algebra">Algebra</option>
                                            <option value="Geometry">Geometry</option>
                                            <option value="Trigonometry">Trigonometry</option>
                                            <option value="Calculus">Calculus</option>
                                            <option value="Statistics">Statistics</option>
                                        </select>
                                        <div class="input-group">
                                            <label>Main File (required)</label>
                                            <div class="popup-box" style="position: relative; overflow: hidden; background-size: cover; background-position: center;">
                                                <div class="content-overlay">
                                                    <button type="button" class="buttons" onclick="triggerUpload(this)">Upload File</button>
                                                </div>
                                                <input type="file" name="main_file" class="file-input" accept="image/*,video/*" style="display: none;" onchange="previewImage(this)" required>
                                            </div>
                                            <p class="hint text-center">Choose File from your device (mp4, png, jpg)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 2: Quiz -->
                        <div class="slide" id="up-quiz">
                            <div class="sm-main">
                                <div class="popup-steps">
                                    <div class="sm-rec"></div>
                                    <div class="sm-div">
                                        <div class="sm-circle">1</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle active">2</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle">3</div>
                                    </div>
                                </div>
                            </div>
            
                            <div class="">
                                <h3 class="popup-title">Quiz (Optional)</h3>
                                <div id="questions-container" class="popup-main0">
                                    
                                    <!-- Question 1 (Default) -->
                                    <div class="popup-main list-card question-card" data-question="1">
                                        <div class="input-group">
                                            <label class="question-label">Question 1</label>
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
                                                        <input type="radio" name="correct_answer[0]" value="0" checked>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <input type="text" name="answers[0][]" class="search-input" placeholder="Input answer">
                                                    <button type="button" class="remove-ans" onclick="this.parentElement.remove(); reorderAnswers(this);" style="background:none; border:none; color:red; cursor:pointer; margin-left:5px;">&times;</button>
                                                </div>
                                                <!-- Answer 2 -->
                                                <div class="search-container ans">
                                                    <label class="checkbox-container">
                                                        <input type="radio" name="correct_answer[0]" value="1">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <input type="text" name="answers[0][]" class="search-input" placeholder="Input answer">
                                                    <button type="button" class="remove-ans" onclick="this.parentElement.remove(); reorderAnswers(this);" style="background:none; border:none; color:red; cursor:pointer; margin-left:5px;">&times;</button>
                                                </div>
                                            </div>
            
                                            <div class="popup-main0 list-card add-answer-btn" onclick="addAnswer(this)">
                                                <div class="text-Mid">+ Add Answer</div>
                                            </div>
                                        </div>
                                        <button type="button" onclick="this.parentElement.remove(); reorderQuestions();" style="color: #ff4d4d; background: none; border: none; cursor: pointer; font-size: 0.8rem; margin-top: 10px;">Remove Question</button>
                                    </div>
                                </div>
            
                                <div class="popup-main0 list-card" onclick="addQuestion()">
                                    <div class="text-Mid">+ Add Question</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 3: Checkup -->
                        <div class="slide" id="up-checkup">
                            <div class="sm-main">
                                <div class="popup-steps">
                                    <div class="sm-rec"></div>
                                    <div class="sm-div">
                                        <div class="sm-circle">1</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle">2</div>
                                    </div>
                                    <div class="sm-div">
                                        <div class="sm-circle active">3</div>
                                    </div>
                                </div>
                            </div>
            
                            <div class="">
                                <h3 class="popup-title">Checkup</h3>
                                <div class="popup-main0">
                                    <div class="list-card">
                                        <div class="input-group">
                                            <label>Preview</label>
                                            <div class="popup-main0">
                                                <div class="popup-colu1">
                                                    <div class="prev">
                                                        <img id="preview-thumbnail" src="default-placeholder.png">
                                                    </div>
                                                    <div class="footer-row">
                                                        <h3 id="preview-title">Title will appear here</h3>
                                                        <span id="preview-category-tag" class="buttons"> </span>
                                                        <div class="bookmark-wrapper" id="bookmarkTrigger">
                                                            <i class="fa-regular fa-bookmark" id="bookmarkIcon"></i>
                                                            <span id="bookmarkCount">0</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p id="preview-desc">
                                                            What is Basic Arithmetic?<br>
                                                            Basic Arithmetic is a branch of mathematics that studies basic number operations. These operations are used in everyday life, from calculating spending money to measuring time. Four main operations form the pillars of arithmetic:<br>
                                                            Basic Properties<br>
                                                            Commutative: Order does not change the result (only applies to + and x).<br>
                                                            a + b = b + a<br>
                                                            Associative: Grouping does not change the result (only applies to + and x).<br>
                                                            (a + b) + c = a + (b + c)<br>
                                                            Distributive: Multiplying a number by the sum in parentheses.<br>
                                                            a x (b + c) = (a x b) + (a x c)
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup-footer">
                    <button id="back" type="button" class="buttons" onclick="moveSlide(-1)">Back</button>
                    <button id="next" type="button" class="buttons" onclick="moveSlide(1)">Next</button>
                    <button id="upload" type="submit" class="buttons">Upload</button>
                </div>
            </div>
        
    </div>
</form>
<!-- Success Modal -->
<div class="success-overlay" id="success">
    <div class="success-card">
        <div class="success-header">
            <button class="circle" onclick="noupModal()">&times;</button>
        </div>
        <div class="success-top">
            <div class="icon-circle">
                <i class="fas fa-check"></i>
            </div>
        </div>
        
        <div class="white-divider">
            <div class="running-white-line"></div>
        </div>
        
        <div class="success-bottom">
            <h1 class="success-text">SUCCESSFULLY UPLOADED</h1>
        </div>
    </div>
</div>

<script>
// Fungsi untuk submit form secara AJAX (opsional, untuk pengalaman lebih baik)
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    // Validasi sebelum submit
    const title = document.getElementById('input-title').value;
    const category = document.getElementById('input-category').value;
    const mainFile = document.querySelector('input[name="main_file"]');
    
    if (!title.trim()) {
        alert('Please enter a title');
        e.preventDefault();
        return;
    }
    
    if (!category) {
        alert('Please select a category');
        e.preventDefault();
        return;
    }
    
    if (!mainFile.files.length) {
        alert('Please upload a main file');
        e.preventDefault();
        return;
    }
    
    // Optional: Tampilkan loading indicator
    document.getElementById('upload').innerHTML = 'Uploading...';
    document.getElementById('upload').disabled = true;
    
    // Jika ingin menggunakan AJAX untuk upload:
    /*
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('save_uploads.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Success:', data);
        // Tampilkan success modal
        document.getElementById("success").style.display="flex";
        document.getElementById("overlay").style.display = "none";
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Upload failed. Please try again.');
    })
    .finally(() => {
        document.getElementById('upload').innerHTML = 'Upload';
        document.getElementById('upload').disabled = false;
    });
    */
});
</script>