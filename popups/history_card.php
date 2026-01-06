<?php
// history_card.php
// Menerima data dari history.php melalui array $quiz_data

// Default values jika data tidak dikirim
$result_id = $quiz_data['result_id'] ?? 0;
$title = $quiz_data['title'] ?? 'Untitled Quiz';
$thumbnail = $quiz_data['thumbnail_path'] ?? '';
$category = $quiz_data['category'] ?? 'General';
$author_name = $quiz_data['author_name'] ?? 'Unknown Author';
$score = $quiz_data['score'] ?? 0;
$correct_answers = $quiz_data['correct_answers'] ?? 0;
$total_questions = $quiz_data['total_questions'] ?? 0;
$finished_at = $quiz_data['finished_at'] ?? date('Y-m-d H:i:s');

// Format tanggal
$date_formatted = date('d/m/Y', strtotime($finished_at));
$time_formatted = date('H:i', strtotime($finished_at));

// Hitung persentase
$percentage = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;

// Tentukan grade berdasarkan persentase
if ($percentage >= 90) $grade = 'A';
elseif ($percentage >= 80) $grade = 'B';
elseif ($percentage >= 70) $grade = 'C';
elseif ($percentage >= 60) $grade = 'D';
else $grade = 'E';

// Tentukan path thumbnail
if (!empty($thumbnail) && strpos($thumbnail, 'uploads/thumbnails/') === false) {
    $thumbnail_path = 'uploads/thumbnails/' . $thumbnail;
} else {
    $thumbnail_path = $thumbnail ?: 'https://via.placeholder.com/100x60/326090/ffffff?text=' . urlencode(substr($title, 0, 10));
}
?>

<div class="list-card" onclick="viewQuizResult(<?php echo $result_id; ?>)">
    <!-- Waktu Quiz -->
    <div class="card-time">
        <i class="fas fa-history"></i>
        <span><?php echo $date_formatted; ?></span>
        <span class="time-detail"><?php echo $time_formatted; ?></span>
    </div>

    <!-- Info Konten -->
    <div class="card-history">
        <div class="thumbnail-placeholder">
            <img src="<?php echo htmlspecialchars($thumbnail_path); ?>" 
                 alt="<?php echo htmlspecialchars($title); ?>"
                 onerror="this.src='https://via.placeholder.com/100x60/326090/ffffff?text=<?php echo urlencode(substr($title, 0, 10)); ?>'">
        </div>
        <div class="info-text">
            <div class="title-row">
                <h3><?php echo htmlspecialchars($title); ?></h3>
            </div>
            <div class="footer-row">
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                    <span class="username-text"><?php echo htmlspecialchars($author_name); ?></span>
                </div>
                <button class="buttons"><?php echo htmlspecialchars($category); ?></button>
            </div>
        </div>
    </div>

    <!-- Statistik Quiz -->
    <div class="card-stats">
        <div class="stat-item">
            <span class="label">Status:</span>
            <span class="status-finished">
                <i class="fas fa-circle" style="color: #1abc9c;"></i> Finished
            </span>
        </div>
        <div class="stat-item">
            <span class="label">Progress:</span>
            <span class="value"><?php echo $correct_answers; ?> / <?php echo $total_questions; ?></span>
        </div>
        <div class="stat-item">
            <span class="label">Correct Answer:</span>
            <span class="value-highlight"><?php echo $percentage; ?>%</span>
        </div>
    </div>

    <!-- Score dan Grade -->
    <div class="card-score">
        <span class="score-label">Score:</span>
        <h1 class="grade"><?php echo $grade; ?></h1>
        <small style="color: var(--minor-orange);"><?php echo $score; ?> points</small>
    </div>
</div>

<script>
// Fungsi untuk melihat detail hasil quiz
function viewQuizResult(resultId) {
    window.location.href = 'quiz_result.php?result_id=' + resultId;
}
</script>