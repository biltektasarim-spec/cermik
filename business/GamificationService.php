<?php
namespace Rehber\Business;

class GamificationService {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Increment user points or check badge milestones
     */
    public function awardPoints(int $user_id, int $points) {
        // Update user points
        $stmt = $this->pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt->execute([$points, $user_id]);

        $this->checkBadges($user_id);
    }

    /**
     * Check if user meets requirement for any unawarded badge
     */
    private function checkBadges(int $user_id) {
        // Query user's current progress
        // Total check_ins
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total_check_ins FROM check_ins WHERE user_id = ? AND status = 'APPROVED'");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch();

        // 1. Loop through badges they don't have yet
        $stmtBadges = $this->pdo->prepare("
            SELECT b.* 
            FROM badges b
            LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
            WHERE ub.user_id IS NULL
        ");
        $stmtBadges->execute([$user_id]);
        $undiscovered_badges = $stmtBadges->fetchAll();

        foreach ($undiscovered_badges as $badge) {
            $award = false;
            if ($badge['requirement_type'] === 'CHECK_IN_COUNT') {
                if ($stats['total_check_ins'] >= $badge['requirement_value']) {
                    $award = true;
                }
            } elseif ($badge['requirement_type'] === 'DISTRICT_SPECIFIC_CHECK_IN') {
                $stmtDistrict = $this->pdo->prepare("
                    SELECT COUNT(*) FROM check_ins 
                    WHERE user_id = ? AND district_id = ? AND status = 'APPROVED'
                ");
                $stmtDistrict->execute([$user_id, $badge['district_id']]);
                if ($stmtDistrict->fetchColumn() >= $badge['requirement_value']) {
                    $award = true;
                }
            }
            
            if ($award) {
                // Award the Badge!
                $stmtAward = $this->pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
                $stmtAward->execute([$user_id, $badge['id']]);
                
                // Add notification
                $msg = "Tebrikler! Yeni bir rozet kazandınız: " . $badge['name'];
                $stmtNotif = $this->pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Yeni Rozet Kazandınız!', ?)");
                $stmtNotif->execute([$user_id, $msg]);
            }
        }
    }
}
?>
