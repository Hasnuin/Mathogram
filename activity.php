<?php
// activity.php
require_once 'Nav/auth_check.php';
require_once 'config.php';

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Ambil data user
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
$join_date = date('d/m/Y', strtotime($user_data['created_at'] ?? 'now'));

// ============================================
// 1. STATISTIK QUIZ FINISHED
// ============================================
$quiz_stats_query = "SELECT 
                        COUNT(*) as total_quizzes,
                        SUM(correct_answers) as total_correct,
                        SUM(total_questions) as total_questions,
                        AVG(score) as avg_score
                     FROM quiz_results 
                     WHERE user_id = '$user_id'";
$quiz_stats_result = mysqli_query($conn, $quiz_stats_query);
$quiz_stats = mysqli_fetch_assoc($quiz_stats_result);

$total_quizzes = $quiz_stats['total_quizzes'] ?? 0;
$total_correct = $quiz_stats['total_correct'] ?? 0;
$total_questions = $quiz_stats['total_questions'] ?? 0;
$avg_score = round($quiz_stats['avg_score'] ?? 0);

// Hitung persentase quiz completed
$quiz_percentage = $total_quizzes > 0 ? min(100, round(($total_quizzes / ($total_quizzes + 5)) * 100)) : 0;

// ============================================
// 2. STATISTIK KATEGORI
// ============================================
$category_stats_query = "SELECT 
                            COUNT(DISTINCT c.category) as total_categories,
                            c.category
                         FROM quiz_results qr
                         JOIN contents c ON qr.content_id = c.id
                         WHERE qr.user_id = '$user_id'
                         GROUP BY c.category";
$category_stats_result = mysqli_query($conn, $category_stats_query);
$total_categories = mysqli_num_rows($category_stats_result);

// Hitung persentase kategori
$category_percentage = $total_categories > 0 ? min(100, round(($total_categories / 6) * 100)) : 0;

// ============================================
// 3. STATISTIK SCORE
// ============================================
$score_stats_query = "SELECT 
                         ROUND(AVG((correct_answers / total_questions) * 100)) as avg_percentage,
                         MAX(score) as highest_score,
                         MIN(score) as lowest_score
                      FROM quiz_results 
                      WHERE user_id = '$user_id' AND total_questions > 0";
$score_stats_result = mysqli_query($conn, $score_stats_query);
$score_stats = mysqli_fetch_assoc($score_stats_result);

$avg_percentage = $score_stats['avg_percentage'] ?? 0;
$highest_score = $score_stats['highest_score'] ?? 0;
$lowest_score = $score_stats['lowest_score'] ?? 0;

// ============================================
// 4. STATISTIK RECENT ACTIVITY
// ============================================
$recent_activity_query = "SELECT 
                             'quiz' as type,
                             qr.finished_at as date,
                             c.title,
                             c.category,
                             qr.score,
                             qr.correct_answers,
                             qr.total_questions
                          FROM quiz_results qr
                          JOIN contents c ON qr.content_id = c.id
                          WHERE qr.user_id = '$user_id'
                          
                          UNION ALL
                          
                          SELECT 
                             'upload' as type,
                             c.created_at as date,
                             c.title,
                             c.category,
                             NULL as score,
                             NULL as correct_answers,
                             NULL as total_questions
                          FROM contents c
                          WHERE c.user_id = '$user_id'
                          
                          ORDER BY date DESC 
                          LIMIT 10";

$recent_activity_result = mysqli_query($conn, $recent_activity_query);
$recent_activities = [];
while ($activity = mysqli_fetch_assoc($recent_activity_result)) {
    $recent_activities[] = $activity;
}

include('Nav/header.php'); 
include('Nav/sidebar.php'); 
include('Nav/topbar.php'); 
?>

<?php include('popups/profile_card.php'); ?>

<style>
.activity-header {
    padding: 20px;
    color: var(--text-color);
    border-bottom: 2px solid var(--primary-blue);
    margin-bottom: 20px;
}

.recent-activity {
    background: var(--nav-color);
    border-radius: 15px;
    padding: 20px;
    margin: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.activity-item {
    display: flex;
    align-items: center;
    padding: 12px;
    margin: 8px 0;
    border-radius: 10px;
    background: var(--bg-color);
    transition: transform 0.2s;
}

.activity-item:hover {
    transform: translateX(5px);
    background: var(--nav-hover);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 18px;
}

.quiz-icon {
    background: var(--primary-blue);
    color: white;
}

.upload-icon {
    background: var(--minor-orange);
    color: white;
}

.activity-details {
    flex: 1;
}

.activity-title {
    font-weight: bold;
    margin-bottom: 3px;
}

.activity-meta {
    font-size: 12px;
    color: var(--text-color);
    opacity: 0.8;
}

.activity-score {
    font-weight: bold;
    color: var(--minor-green);
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--text-color);
}

.chart-container {
    position: relative;
}

.chart-label {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}
</style>

<div class="activity-header">
    <h2>Activity Dashboard</h2>
    <p>Track your learning progress and statistics</p>
</div>

<div class="history">
    <div class="stats-dashboard">
        <!-- CARD 1: TOTAL QUIZ FINISHED -->
        <div class="stat-card">
            <h3 class="stat-label">TOTAL QUIZ FINISHED</h3>
            <div class="chart-container">
                <div class="pi-chart" id="chart-quiz" style="--p:<?php echo $quiz_percentage; ?>; --c:var(--minor-orange);">
                    <div class="inner-circle">
                        <span class="main-number"><?php echo $total_quizzes; ?></span>
                        <span class="sub-text">Quiz completed</span>
                    </div>
                </div>
            </div>
            <div class="legend">
                <div class="legend-item">
                    <span class="dot" style="background: var(--minor-orange);"></span> 
                    <span>Completed: <?php echo $total_quizzes; ?></span>
                </div>
                <div class="legend-item">
                    <span class="dot" style="background: var(--gray-circle);"></span> 
                    <span>Remaining</span>
                </div>
                <div class="stat-item" style="margin-top: 10px;">
                    <span class="label">Correct Answers:</span>
                    <span class="value"><?php echo $total_correct; ?>/<?php echo $total_questions; ?></span>
                </div>
            </div>
        </div>
    
        <!-- CARD 2: CATEGORY STATS -->
        <div class="stat-card">
            <h3 class="stat-label">CATEGORIES</h3>
            <div class="chart-container">
                <div class="pi-chart" id="chart-category" style="--p:<?php echo $category_percentage; ?>; --c:#3498db;">
                    <div class="inner-circle">
                        <span class="main-number"><?php echo $total_categories; ?></span>
                        <span class="sub-text">Active categories</span>
                    </div>
                </div>
            </div>
            <div class="legend" style="margin-top: 40px;">
                <?php if ($total_categories > 0): ?>
                    <?php mysqli_data_seek($category_stats_result, 0); ?>
                    <?php while ($category = mysqli_fetch_assoc($category_stats_result)): ?>
                        <div class="legend-item">
                            <span class="dot" style="background: #3498db;"></span> 
                            <?php echo htmlspecialchars($category['category']); ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="legend-item">
                        <span class="dot" style="background: #ccc;"></span> 
                        No categories yet
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
        <!-- CARD 3: AVERAGE SCORE -->
        <div class="stat-card">
            <h3 class="stat-label">PERFORMANCE</h3>
            <div class="chart-container">
                <div class="pi-chart" id="chart-score" style="--p:<?php echo $avg_percentage; ?>; --c:#1abc9c;">
                    <div class="inner-circle">
                        <span class="main-number"><?php echo $avg_percentage; ?>%</span>
                        <span class="sub-text">Average score</span>
                    </div>
                </div>
            </div>
            <div class="legend">
                <div class="legend-item">
                    <span class="dot correct"></span> 
                    <span>Avg Score: <?php echo $avg_score; ?> pts</span>
                </div>
                <div class="legend-item">
                    <span class="dot total"></span> 
                    <span>Highest: <?php echo $highest_score; ?> pts</span>
                </div>
                <div class="legend-item">
                    <span class="dot" style="background: #e74c3c;"></span> 
                    <span>Lowest: <?php echo $lowest_score; ?> pts</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RECENT ACTIVITY SECTION -->
    <div class="recent-activity">
        <h3 style="margin-bottom: 20px; color: var(--text-color);">
            <i class="fas fa-history"></i> Recent Activity
        </h3>
        
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon <?php echo $activity['type'] == 'quiz' ? 'quiz-icon' : 'upload-icon'; ?>">
                        <?php if ($activity['type'] == 'quiz'): ?>
                            <i class="fas fa-question-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-upload"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="activity-details">
                        <div class="activity-title">
                            <?php echo htmlspecialchars($activity['title']); ?>
                        </div>
                        <div class="activity-meta">
                            <span>
                                <?php echo $activity['type'] == 'quiz' ? 'Completed quiz' : 'Uploaded content'; ?> • 
                                <?php echo htmlspecialchars($activity['category']); ?> • 
                                <?php echo date('d M Y, H:i', strtotime($activity['date'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($activity['type'] == 'quiz' && isset($activity['score'])): ?>
                        <div class="activity-score">
                            <?php 
                            $percentage = $activity['total_questions'] > 0 ? 
                                round(($activity['correct_answers'] / $activity['total_questions']) * 100) : 0;
                            ?>
                            <?php echo $percentage; ?>%
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <button class="buttons" onclick="window.location.href='history.php'">
                    View All History <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chart-line fa-3x" style="color: var(--nav-hover); margin-bottom: 20px;"></i>
                <h4>No Activity Yet</h4>
                <p>Start taking quizzes or uploading content to see your activity here.</p>
                <button class="buttons" onclick="window.location.href='dashboard.php'">
                    <i class="fas fa-rocket"></i> Start Exploring
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Animate charts on page load
document.addEventListener('DOMContentLoaded', function() {
    const charts = document.querySelectorAll('.pi-chart');
    
    charts.forEach(chart => {
        const computedStyle = getComputedStyle(chart);
        const targetValue = parseFloat(computedStyle.getPropertyValue('--p'));
        let startValue = 0;
        const duration = 1500;
        const stepTime = Math.abs(Math.floor(duration / targetValue));
        
        // Reset chart to 0
        chart.style.setProperty('--p', 0);
        
        // Animate chart
        const timer = setInterval(() => {
            startValue++;
            chart.style.setProperty('--p', startValue);
            
            if (startValue >= targetValue) {
                clearInterval(timer);
            }
        }, stepTime);
    });
    
    // Animate numbers
    const numberElements = document.querySelectorAll('.main-number');
    numberElements.forEach(element => {
        const finalValue = parseInt(element.textContent);
        let startValue = 0;
        const duration = 1000;
        const increment = finalValue / (duration / 16); // 60fps
        
        const numberTimer = setInterval(() => {
            startValue += increment;
            if (startValue >= finalValue) {
                element.textContent = finalValue;
                clearInterval(numberTimer);
            } else {
                element.textContent = Math.floor(startValue);
            }
        }, 16);
    });
});
</script>

<?php include('upload.php'); ?>
<?php include('Nav/footer.php'); ?>

<script src="js/script.js"></script>