<?php
// edit_content.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['id'];

// Validasi content_id
if ($content_id <= 0) {
    die("Invalid content ID");
}

// ============================================
// AMBIL DATA KONTEN YANG AKAN DIEDIT
// ============================================
$query = "SELECT * FROM contents WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $content_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Content not found or you don't have permission to edit");
}

$content = mysqli_fetch_assoc($result);

// ============================================
// AMBIL QUIZ JIKA ADA
// ============================================
$quiz_query = "SELECT q.*, 
               (SELECT GROUP_CONCAT(a.id, ':', a.answer_text, ':', a.is_correct ORDER BY a.answer_order) 
                FROM quiz_answers a 
                WHERE a.question_id = q.id) as answers
               FROM quiz_questions q 
               WHERE q.content_id = ?
               ORDER BY q.question_order";
$stmt = mysqli_prepare($conn, $quiz_query);
mysqli_stmt_bind_param($stmt, "i", $content_id);
mysqli_stmt_execute($stmt);
$quiz_result = mysqli_stmt_get_result($stmt);

$questions = [];
while ($question = mysqli_fetch_assoc($quiz_result)) {
    // Parse answers
    $answers = [];
    if (!empty($question['answers'])) {
        $answer_parts = explode(',', $question['answers']);
        foreach ($answer_parts as $part) {
            list($answer_id, $answer_text, $is_correct) = explode(':', $part, 3);
            $answers[] = [
                'id' => $answer_id,
                'text' => $answer_text,
                'is_correct' => $is_correct
            ];
        }
    }
    $question['answers'] = $answers;
    $questions[] = $question;
}

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<style>
.edit-content-container {
    padding: 20px;
    max-width: 1000px;
    margin: 0 auto;
}

.edit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-blue);
}

.edit-header h1 {
    color: var(--text-color);
    margin: 0;
}

.edit-form {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--nav-hover);
}

.form-section h3 {
    color: var(--text-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: var(--text-color);
}

.form-group .required::after {
    content: " *";
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--minor-orange);
    border-radius: 8px;
    background: var(--bg-color);
    color: var(--text-color);
    font-family: inherit;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 2px rgba(50, 96, 144, 0.2);
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.file-preview {
    margin-top: 10px;
    border-radius: 10px;
    overflow: hidden;
    max-width: 300px;
}

.file-preview img,
.file-preview video {
    width: 100%;
    max-height: 200px;
    object-fit: contain;
    background: #000;
}

.current-file {
    margin-top: 5px;
    font-size: 12px;
    color: var(--text-color);
    opacity: 0.7;
}

.question-item {
    background: var(--bg-color);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid var(--nav-hover);
}

.answer-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    padding: 10px;
    background: rgba(255,255,255,0.05);
    border-radius: 6px;
}

.answer-item input[type="radio"] {
    margin: 0;
}

.answer-text {
    flex: 1;
}

.btn-remove {
    background: #e74c3c;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-add {
    background: var(--minor-green);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.btn-remove-question {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 10px;
    font-size: 12px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--nav-hover);
}

.btn-save {
    background: var(--minor-green);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
}

.btn-cancel {
    background: var(--nav-hover);
    color: var(--text-color);
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 16px;
}

.error-message {
    background: #e74c3c;
    color: white;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: none;
}
</style>

<div class="edit-content-container">
    <div class="edit-header">
        <h1><i class="fas fa-edit"></i> Edit Content</h1>
        <button class="buttons" onclick="window.location.href='view_content.php?id=<?php echo $content_id; ?>'">
            <i class="fas fa-eye"></i> Preview
        </button>
    </div>

    <form id="editContentForm" class="edit-form" action="update_content.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
        
        <div class="error-message" id="errorMessage"></div>

        <!-- Section 1: Basic Information -->
        <div class="form-section">
            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="required">Title</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($content['title']); ?>" 
                           required maxlength="100">
                    <small style="color: var(--text-color); opacity: 0.7;">Max 100 characters</small>
                </div>
                
                <div class="form-group">
                    <label class="required">Category</label>
                    <select name="category" class="form-control" required>
                        <option value="">Select Category</option>
                        <option value="Basic Math" <?php echo $content['category'] == 'Basic Math' ? 'selected' : ''; ?>>Basic Math</option>
                        <option value="Algebra" <?php echo $content['category'] == 'Algebra' ? 'selected' : ''; ?>>Algebra</option>
                        <option value="Geometry" <?php echo $content['category'] == 'Geometry' ? 'selected' : ''; ?>>Geometry</option>
                        <option value="Trigonometry" <?php echo $content['category'] == 'Trigonometry' ? 'selected' : ''; ?>>Trigonometry</option>
                        <option value="Calculus" <?php echo $content['category'] == 'Calculus' ? 'selected' : ''; ?>>Calculus</option>
                        <option value="Statistics" <?php echo $content['category'] == 'Statistics' ? 'selected' : ''; ?>>Statistics</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($content['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="draft" <?php echo $content['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $content['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo $content['status'] == 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
        </div>

        <!-- Section 2: Media Files -->
        <div class="form-section">
            <h3><i class="fas fa-file-image"></i> Media Files</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    <?php if (!empty($content['thumbnail_path'])): ?>
                        <div class="current-file">
                            Current: <?php echo basename($content['thumbnail_path']); ?>
                        </div>
                        <div class="file-preview">
                            <?php 
                            $thumb_path = strpos($content['thumbnail_path'], 'uploads/thumbnails/') === 0 ? 
                                $content['thumbnail_path'] : 'uploads/thumbnails/' . $content['thumbnail_path'];
                            ?>
                            <img src="<?php echo htmlspecialchars($thumb_path); ?>" 
                                 alt="Current thumbnail"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                    <small style="color: var(--text-color); opacity: 0.7;">Leave empty to keep current thumbnail</small>
                </div>
                
                <div class="form-group">
                    <label>Main File</label>
                    <input type="file" name="main_file" class="form-control" accept="image/*,video/*">
                    <?php if (!empty($content['file_path'])): ?>
                        <div class="current-file">
                            Current: <?php echo basename($content['file_path']); ?> (<?php echo $content['file_type']; ?>)
                        </div>
                        <div class="file-preview">
                            <?php 
                            $file_path = strpos($content['file_path'], 'uploads/content/') === 0 ? 
                                $content['file_path'] : 'uploads/content/' . $content['file_path'];
                            
                            if ($content['file_type'] == 'video'): ?>
                                <video controls style="width:100%;">
                                    <source src="<?php echo htmlspecialchars($file_path); ?>">
                                </video>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($file_path); ?>" 
                                     alt="Current file"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <small style="color: var(--text-color); opacity: 0.7;">Leave empty to keep current file</small>
                </div>
            </div>
        </div>

        <!-- Section 3: Quiz Questions -->
        <div class="form-section">
            <h3><i class="fas fa-question-circle"></i> Quiz Questions (Optional)</h3>
            <div id="questionsContainer">
                <?php foreach ($questions as $qIndex => $question): ?>
                    <div class="question-item" data-question-index="<?php echo $qIndex; ?>">
                        <div class="form-group">
                            <label>Question <?php echo $qIndex + 1; ?></label>
                            <input type="hidden" name="question_id[]" value="<?php echo $question['id']; ?>">
                            <textarea name="question_text[]" class="form-control" rows="3" placeholder="Enter question text"><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Question Image (Optional)</label>
                            <input type="file" name="question_img[]" class="form-control" accept="image/*">
                            <?php if (!empty($question['question_file'])): ?>
                                <div class="current-file">
                                    Current: <?php echo basename($question['question_file']); ?>
                                </div>
                                <div class="file-preview">
                                    <?php 
                                    $q_file_path = strpos($question['question_file'], 'uploads/questions/') === 0 ? 
                                        $question['question_file'] : 'uploads/questions/' . $question['question_file'];
                                    ?>
                                    <img src="<?php echo htmlspecialchars($q_file_path); ?>" 
                                         alt="Question image"
                                         onerror="this.style.display='none'">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Points</label>
                            <input type="number" name="points[]" class="form-control" value="<?php echo $question['points']; ?>" min="1" max="10">
                        </div>
                        
                        <div class="form-group">
                            <label>Answers (Select the correct answer)</label>
                            <div id="answersContainer-<?php echo $qIndex; ?>">
                                <?php foreach ($question['answers'] as $aIndex => $answer): ?>
                                    <div class="answer-item">
                                        <input type="radio" name="correct_answer[<?php echo $qIndex; ?>]" 
                                               value="<?php echo $aIndex; ?>" 
                                               <?php echo $answer['is_correct'] == 1 ? 'checked' : ''; ?>>
                                        <input type="hidden" name="answer_id[<?php echo $qIndex; ?>][]" value="<?php echo $answer['id']; ?>">
                                        <input type="text" name="answers[<?php echo $qIndex; ?>][]" 
                                               class="form-control answer-text" 
                                               value="<?php echo htmlspecialchars($answer['text']); ?>" 
                                               placeholder="Answer text" required>
                                        <button type="button" class="btn-remove" onclick="removeAnswer(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn-add" onclick="addAnswer(<?php echo $qIndex; ?>)">
                                <i class="fas fa-plus"></i> Add Answer
                            </button>
                        </div>
                        
                        <button type="button" class="btn-remove-question" onclick="removeQuestion(this)">
                            <i class="fas fa-trash"></i> Remove Question
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn-add" onclick="addQuestion()">
                <i class="fas fa-plus-circle"></i> Add New Question
            </button>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="window.location.href='view_content.php?id=<?php echo $content_id; ?>'">
                Cancel
            </button>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<script>
let questionCount = <?php echo count($questions); ?>;

// Fungsi untuk menambah pertanyaan baru
function addQuestion() {
    const container = document.getElementById('questionsContainer');
    const questionIndex = questionCount;
    
    const questionHTML = `
        <div class="question-item" data-question-index="${questionIndex}">
            <div class="form-group">
                <label>Question ${questionIndex + 1}</label>
                <input type="hidden" name="question_id[]" value="0">
                <textarea name="question_text[]" class="form-control" rows="3" placeholder="Enter question text"></textarea>
            </div>
            
            <div class="form-group">
                <label>Question Image (Optional)</label>
                <input type="file" name="question_img[]" class="form-control" accept="image/*">
            </div>
            
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="points[]" class="form-control" value="1" min="1" max="10">
            </div>
            
            <div class="form-group">
                <label>Answers (Select the correct answer)</label>
                <div id="answersContainer-${questionIndex}">
                    <div class="answer-item">
                        <input type="radio" name="correct_answer[${questionIndex}]" value="0" checked>
                        <input type="hidden" name="answer_id[${questionIndex}][]" value="0">
                        <input type="text" name="answers[${questionIndex}][]" class="form-control answer-text" placeholder="Answer text" required>
                        <button type="button" class="btn-remove" onclick="removeAnswer(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="answer-item">
                        <input type="radio" name="correct_answer[${questionIndex}]" value="1">
                        <input type="hidden" name="answer_id[${questionIndex}][]" value="0">
                        <input type="text" name="answers[${questionIndex}][]" class="form-control answer-text" placeholder="Answer text" required>
                        <button type="button" class="btn-remove" onclick="removeAnswer(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-add" onclick="addAnswer(${questionIndex})">
                    <i class="fas fa-plus"></i> Add Answer
                </button>
            </div>
            
            <button type="button" class="btn-remove-question" onclick="removeQuestion(this)">
                <i class="fas fa-trash"></i> Remove Question
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', questionHTML);
    questionCount++;
}

// Fungsi untuk menambah jawaban
function addAnswer(questionIndex) {
    const container = document.getElementById(`answersContainer-${questionIndex}`);
    const answerCount = container.querySelectorAll('.answer-item').length;
    
    const answerHTML = `
        <div class="answer-item">
            <input type="radio" name="correct_answer[${questionIndex}]" value="${answerCount}">
            <input type="hidden" name="answer_id[${questionIndex}][]" value="0">
            <input type="text" name="answers[${questionIndex}][]" class="form-control answer-text" placeholder="Answer text" required>
            <button type="button" class="btn-remove" onclick="removeAnswer(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', answerHTML);
}

// Fungsi untuk menghapus jawaban
function removeAnswer(button) {
    const answerItem = button.closest('.answer-item');
    const container = answerItem.parentElement;
    const answerItems = container.querySelectorAll('.answer-item');
    
    // Jangan hapus jika hanya ada 2 jawaban
    if (answerItems.length <= 2) {
        alert('Each question must have at least 2 answers');
        return;
    }
    
    answerItem.remove();
    
    // Update radio button values
    updateRadioValues(container);
}

// Fungsi untuk update nilai radio button
function updateRadioValues(container) {
    const answerItems = container.querySelectorAll('.answer-item');
    answerItems.forEach((item, index) => {
        const radio = item.querySelector('input[type="radio"]');
        radio.value = index;
    });
}

// Fungsi untuk menghapus pertanyaan
function removeQuestion(button) {
    const questionItem = button.closest('.question-item');
    
    // Konfirmasi penghapusan
    if (!confirm('Are you sure you want to remove this question?')) {
        return;
    }
    
    questionItem.remove();
    
    // Update nomor pertanyaan
    updateQuestionNumbers();
}

// Fungsi untuk update nomor pertanyaan
function updateQuestionNumbers() {
    const questionItems = document.querySelectorAll('.question-item');
    questionItems.forEach((item, index) => {
        const label = item.querySelector('label');
        label.textContent = `Question ${index + 1}`;
        
        // Update data attribute
        item.setAttribute('data-question-index', index);
        
        // Update input names
        const questionIndex = index;
        const textarea = item.querySelector('textarea[name="question_text[]"]');
        const fileInput = item.querySelector('input[name="question_img[]"]');
        const pointsInput = item.querySelector('input[name="points[]"]');
        
        // Update answer containers
        const answerContainer = item.querySelector('[id^="answersContainer-"]');
        if (answerContainer) {
            answerContainer.id = `answersContainer-${questionIndex}`;
            
            // Update radio button names
            const radios = answerContainer.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.name = `correct_answer[${questionIndex}]`;
            });
            
            // Update answer input names
            const answerInputs = answerContainer.querySelectorAll('input[name^="answers["]');
            answerInputs.forEach(input => {
                input.name = `answers[${questionIndex}][]`;
            });
            
            // Update answer_id input names
            const answerIdInputs = answerContainer.querySelectorAll('input[name^="answer_id["]');
            answerIdInputs.forEach(input => {
                input.name = `answer_id[${questionIndex}][]`;
            });
            
            // Update add button onclick
            const addButton = item.querySelector('.btn-add');
            if (addButton) {
                addButton.setAttribute('onclick', `addAnswer(${questionIndex})`);
            }
        }
    });
    
    questionCount = questionItems.length;
}

// Validasi form sebelum submit
document.getElementById('editContentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.style.display = 'none';
    
    // Validasi judul
    const title = this.querySelector('input[name="title"]').value.trim();
    if (!title) {
        showError('Title is required');
        return;
    }
    
    // Validasi kategori
    const category = this.querySelector('select[name="category"]').value;
    if (!category) {
        showError('Category is required');
        return;
    }
    
    // Validasi setiap pertanyaan
    const questionItems = document.querySelectorAll('.question-item');
    for (let item of questionItems) {
        const questionText = item.querySelector('textarea[name="question_text[]"]').value.trim();
        if (!questionText) {
            showError('All questions must have text');
            return;
        }
        
        const answers = item.querySelectorAll('input[name^="answers["]');
        if (answers.length < 2) {
            showError('Each question must have at least 2 answers');
            return;
        }
        
        let hasAnswerText = true;
        answers.forEach(answer => {
            if (!answer.value.trim()) {
                hasAnswerText = false;
            }
        });
        
        if (!hasAnswerText) {
            showError('All answers must have text');
            return;
        }
    }
    
    // Jika semua valid, submit form
    this.submit();
});

function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth' });
}

// Preview file sebelum upload
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function(e) {
        const file = this.files[0];
        if (!file) return;
        
        // Validasi ukuran file (max 100MB)
        if (file.size > 100 * 1024 * 1024) {
            alert('File is too large! Maximum size is 100MB.');
            this.value = '';
            return;
        }
        
        // Preview untuk gambar
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.innerHTML = `<img src="${e.target.result}" style="width:100%; max-height:200px; object-fit:contain;">`;
                
                const parent = input.parentElement;
                const existingPreview = parent.querySelector('.file-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                parent.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
        
        // Preview untuk video
        if (file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.innerHTML = `
                    <video controls style="width:100%; max-height:200px;">
                        <source src="${e.target.result}" type="${file.type}">
                    </video>
                `;
                
                const parent = input.parentElement;
                const existingPreview = parent.querySelector('.file-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                parent.appendChild(preview);
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>