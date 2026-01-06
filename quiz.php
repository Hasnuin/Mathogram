<?php 
// quiz.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$current_user_id = $_SESSION['id'];
$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;

// Validasi content_id
if ($content_id <= 0) {
    die("Invalid content ID");
}

// ============================================
// 1. AMBIL DATA KONTEN
// ============================================
$content_query = "SELECT c.*, u.username 
                  FROM contents c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.id = '$content_id'";
$content_result = mysqli_query($conn, $content_query);

if (!$content_result || mysqli_num_rows($content_result) == 0) {
    die("Content not found");
}

$content = mysqli_fetch_assoc($content_result);

// ============================================
// 2. AMBIL SOAL-SOAL QUIZ
// ============================================
$questions_query = "SELECT * FROM quiz_questions 
                    WHERE content_id = '$content_id' 
                    ORDER BY question_order ASC";
$questions_result = mysqli_query($conn, $questions_query);
$questions_count = mysqli_num_rows($questions_result);

// ============================================
// 3. AMBIL JAWABAN UNTUK SETIAP SOAL
// ============================================
$questions = [];
while ($question = mysqli_fetch_assoc($questions_result)) {
    $question_id = $question['id'];
    
    // Ambil jawaban untuk soal ini
    $answers_query = "SELECT * FROM quiz_answers 
                      WHERE question_id = '$question_id' 
                      ORDER BY answer_order ASC";
    $answers_result = mysqli_query($conn, $answers_query);
    
    $answers = [];
    while ($answer = mysqli_fetch_assoc($answers_result)) {
        $answers[] = $answer;
    }
    
    $question['answers'] = $answers;
    $questions[] = $question;
}

// Hitung total poin
$total_points = 0;
foreach ($questions as $question) {
    $total_points += $question['points'];
}

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<!-- CSS Tambahan untuk Quiz -->
<style>
.quiz-container {
    padding: 20px;
}

.question-card {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    color: var(--text-color);
}

.question-image {
    max-width: 100%;
    max-height: 300px;
    border-radius: 10px;
    margin: 10px 0;
    display: block;
}

.answer-label {
    display: flex;
    align-items: center;
    margin: 10px 0;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.3s;
    gap : 12px;
    border: 2px solid transparent;

}

.answer-label:hover {
    background: var(--nav-hover);
}

.answer-label.selected {
    border-color: var(--primary-blue);
    background: rgba(50, 96, 144, 0.1);
}

.answer-radio {
    margin-right: 10px;
    transform: scale(1.2);
}

.answer-text {
    flex: 1;
    font-size: 16px;
    
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--nav-hover);
    border-radius: 4px;
    margin: 20px 0;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-blue);
    width: 0%;
    transition: width 0.3s;
}

.quiz-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.current-question {
    font-weight: bold;
    color: var(--minor-orange);
}
</style>

<!-- HERO BANNER DENGAN DATA KONTEN -->
<div class="hero-banner d-flex align-items-end">
    <?php if ($content['thumbnail_path']): ?>
        <?php 
        $thumbnail_path = strpos($content['thumbnail_path'], 'uploads/thumbnails/') === 0 ? 
            $content['thumbnail_path'] : 'uploads/thumbnails/' . $content['thumbnail_path'];
        ?>
        <img src="<?php echo htmlspecialchars($thumbnail_path); ?>" 
             alt="<?php echo htmlspecialchars($content['title']); ?>"
             onerror="this.src='https://via.placeholder.com/800x200/326090/ffffff?text=<?php echo urlencode($content['title']); ?>'">
    <?php else: ?>
        <img src="https://via.placeholder.com/800x200/326090/ffffff?text=<?php echo urlencode($content['title']); ?>">
    <?php endif; ?>
    
    <div class="hero-overlay w-100 d-flex justify-content-between align-items-center">
        <div class="banner-img">
            <h5 class=""><?php echo htmlspecialchars($content['title']); ?></h5>
            <small><?php echo htmlspecialchars($content['username']); ?></small>
        </div>
        <div class="quiz-info">
            <small>Questions: <?php echo $questions_count; ?> | Points: <?php echo $total_points; ?></small>
        </div>
    </div>
</div>

<!-- CONTAINER QUIZ -->
<div class="quiz-container">
    <?php if ($questions_count > 0): ?>
        <form id="quizForm" method="POST" action="submit_quiz.php">
            <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
            <input type="hidden" name="user_id" value="<?php echo $current_user_id; ?>">
            
            <div class="progress-bar">
                <div class="progress-fill" id="quizProgress" style="width: <?php echo round((1/$questions_count)*100); ?>%"></div>
            </div>
            
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" id="question-<?php echo $index + 1; ?>" 
                     style="<?php echo $index > 0 ? 'display:none;' : ''; ?>">
                    
                    <!-- Nomor Soal -->
                    <div class="quiz-number" style="display: inline-block; margin-bottom: 15px;">
                        <?php echo $index + 1; ?>
                    </div>
                    
                    <!-- Gambar Soal (jika ada) -->
                    <?php if (!empty($question['question_file'])): ?>
                        <?php 
                        $question_file = strpos($question['question_file'], 'uploads/questions/') === 0 ? 
                            $question['question_file'] : 'uploads/questions/' . $question['question_file'];
                        ?>
                        <img src="<?php echo htmlspecialchars($question_file); ?>" 
                             class="question-image"
                             alt="Question image"
                             onerror="this.style.display='none'">
                    <?php endif; ?>
                    
                    <!-- Teks Soal -->
                    <div class="question-text" style="margin: 15px 0; font-size: 18px;">
                        <?php echo nl2br(htmlspecialchars($question['question_text'])); ?>
                        <small style="color: var(--minor-orange); float: right;">
                            (<?php echo $question['points']; ?> point<?php echo $question['points'] > 1 ? 's' : ''; ?>)
                        </small>
                    </div>
                    
                    <!-- Pilihan Jawaban -->
                    <div class="answers-container">
                        <?php foreach ($question['answers'] as $ans_index => $answer): ?>
                            <label class="answer-label" 
                                   for="answer-<?php echo $question['id']; ?>-<?php echo $ans_index; ?>"
                                   onclick="selectAnswer(this, <?php echo $question['id']; ?>, <?php echo $ans_index; ?>)">
                                <input type="radio" 
                                       class="answer-radio"
                                       id="answer-<?php echo $question['id']; ?>-<?php echo $ans_index; ?>"
                                       name="answers[<?php echo $question['id']; ?>]"
                                       value="<?php echo $answer['id']; ?>"
                                       style="display: none;">
                                <span class="custom-radio"></span>
                                <div class="answer-text">
                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Navigasi -->
                    <div class="quiz-navigation">
                        <div class="current-question">
                            Question <?php echo $index + 1; ?> of <?php echo $questions_count; ?>
                        </div>
                        <div>
                            <?php if ($index > 0): ?>
                                <button type="button" class="buttons" onclick="prevQuestion()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($index < $questions_count - 1): ?>
                                <button type="button" class="buttons" onclick="nextQuestion()">
                                    Next <i class="fas fa-arrow-right"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" class="buttons" style="background: var(--minor-green);">
                                    <i class="fas fa-paper-plane"></i> Submit Quiz
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </form>
    <?php else: ?>
        <div class= "sub-text"style="text-align: center; padding: 40px;">
            <h3>No quiz available for this content</h3>
            <p>This content doesn't have any quiz questions yet.</p>
            <button class="buttons" onclick="window.history.back()">Go Back</button>
        </div>
    <?php endif; ?>
</div>

<!-- JavaScript untuk Navigasi Quiz -->
<script>
let currentQuestionIndex = 0;
const totalQuestions = <?php echo $questions_count; ?>;
const userAnswers = {};

function showQuestion(index) {
    // Sembunyikan semua soal
    document.querySelectorAll('.question-card').forEach(card => {
        card.style.display = 'none';
    });
    
    // Tampilkan soal yang dipilih
    const questionCard = document.getElementById('question-' + (index + 1));
    if (questionCard) {
        questionCard.style.display = 'block';
    }
    
    // Update progress bar
    const progressPercent = ((index + 1) / totalQuestions) * 100;
    document.getElementById('quizProgress').style.width = progressPercent + '%';
    
    // Highlight jawaban yang sudah dipilih (jika ada)
    const questionId = questionCard.querySelector('input[type="radio"]')?.name.match(/\[(\d+)\]/)?.[1];
    if (questionId && userAnswers[questionId] !== undefined) {
        const selectedLabel = document.querySelector(`label[for="answer-${questionId}-${userAnswers[questionId]}"]`);
        if (selectedLabel) {
            selectAnswer(selectedLabel, parseInt(questionId), userAnswers[questionId]);
        }
    }
}

function selectAnswer(labelElement, questionId, answerIndex) {
    // Hapus seleksi dari semua jawaban di soal ini
    const questionCard = labelElement.closest('.question-card');
    questionCard.querySelectorAll('.answer-label').forEach(label => {
        label.classList.remove('selected');
    });
    
    // Tandai jawaban yang dipilih
    labelElement.classList.add('selected');
    
    // Simpan jawaban user
    userAnswers[questionId] = answerIndex;
    
    // Tandai radio button sebagai checked
    const radioInput = document.getElementById(`answer-${questionId}-${answerIndex}`);
    if (radioInput) {
        radioInput.checked = true;
    }
    
    // Auto next setelah 1 detik (opsional)
    // setTimeout(() => {
    //     if (currentQuestionIndex < totalQuestions - 1) {
    //         nextQuestion();
    //     }
    // }, 1000);
}

function nextQuestion() {
    if (currentQuestionIndex < totalQuestions - 1) {
        currentQuestionIndex++;
        showQuestion(currentQuestionIndex);
    }
}

function prevQuestion() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        showQuestion(currentQuestionIndex);
    }
}

// Inisialisasi quiz
document.addEventListener('DOMContentLoaded', function() {
    showQuestion(0);
    
    // Validasi sebelum submit
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        const answeredQuestions = Object.keys(userAnswers).length;
        
        if (answeredQuestions < totalQuestions) {
            e.preventDefault();
            const confirmSubmit = confirm(`You have answered ${answeredQuestions} out of ${totalQuestions} questions. Are you sure you want to submit?`);
            
            if (confirmSubmit) {
                // Lanjutkan submit
                this.submit();
            }
        }
    });
});

// Navigasi dengan keyboard
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight') {
        nextQuestion();
    } else if (e.key === 'ArrowLeft') {
        prevQuestion();
    } else if (e.key >= '1' && e.key <= '9') {
        // Memilih jawaban dengan angka 1-9
        const answerIndex = parseInt(e.key) - 1;
        const currentQuestion = document.querySelector('.question-card[style*="display: block"]');
        if (currentQuestion) {
            const answerLabels = currentQuestion.querySelectorAll('.answer-label');
            if (answerIndex < answerLabels.length) {
                const questionId = answerLabels[0].querySelector('input').name.match(/\[(\d+)\]/)?.[1];
                if (questionId) {
                    selectAnswer(answerLabels[answerIndex], parseInt(questionId), answerIndex);
                }
            }
        }
    }
});
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>
<script src="js/script.js"></script>