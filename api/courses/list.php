<?php
// api/courses/list.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../includes/functions.php';

try {
    $db = getDB();
    
    // Pagination
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(1, intval($_GET['limit'] ?? 12)));
    $offset = ($page - 1) * $limit;
    
    // Filters
    $category = sanitize($_GET['category'] ?? '');
    $search = sanitize($_GET['search'] ?? '');
    $level = sanitize($_GET['level'] ?? '');
    $tutor = intval($_GET['tutor'] ?? 0);
    $minPrice = floatval($_GET['min_price'] ?? 0);
    $maxPrice = floatval($_GET['max_price'] ?? 0);
    $sort = sanitize($_GET['sort'] ?? 'newest');
    $featured = isset($_GET['featured']) ? 1 : null;
    
    // Build query
    $where = ["c.is_published = 1"];
    $params = [];
    
    if ($category) {
        $where[] = "cat.slug = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $where[] = "(c.title LIKE ? OR c.description LIKE ? OR c.short_description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($level && in_array($level, ['beginner', 'intermediate', 'advanced'])) {
        $where[] = "c.level = ?";
        $params[] = $level;
    }
    
    if ($tutor > 0) {
        $where[] = "c.tutor_id = ?";
        $params[] = $tutor;
    }
    
    if ($minPrice > 0) {
        $where[] = "COALESCE(c.discount_price, c.price) >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice > 0) {
        $where[] = "COALESCE(c.discount_price, c.price) <= ?";
        $params[] = $maxPrice;
    }
    
    if ($featured !== null) {
        $where[] = "c.is_featured = 1";
    }
    
    $whereClause = implode(' AND ', $where);
    
    // Sort
    $orderBy = match($sort) {
        'price_low' => 'COALESCE(c.discount_price, c.price) ASC',
        'price_high' => 'COALESCE(c.discount_price, c.price) DESC',
        'popular' => 'enrollment_count DESC',
        'rating' => 'avg_rating DESC',
        'oldest' => 'c.created_at ASC',
        default => 'c.created_at DESC'
    };
    
    // Get total count
    $countSql = "
        SELECT COUNT(*) as total 
        FROM courses c
        JOIN categories cat ON c.category_id = cat.id
        WHERE $whereClause
    ";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get courses
    $sql = "
        SELECT 
            c.id,
            c.title,
            c.slug,
            c.short_description,
            c.thumbnail,
            c.price,
            c.discount_price,
            c.level,
            c.duration_hours,
            c.total_lessons,
            c.created_at,
            cat.name as category_name,
            cat.slug as category_slug,
            u.id as tutor_id,
            u.name as tutor_name,
            u.avatar as tutor_avatar,
            (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as enrollment_count,
            (SELECT AVG(rating) FROM reviews WHERE course_id = c.id AND is_approved = 1) as avg_rating,
            (SELECT COUNT(*) FROM reviews WHERE course_id = c.id AND is_approved = 1) as review_count
        FROM courses c
        JOIN categories cat ON c.category_id = cat.id
        JOIN users u ON c.tutor_id = u.id
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    // Format data
    foreach ($courses as &$course) {
        $course['price'] = floatval($course['price']);
        $course['discount_price'] = $course['discount_price'] ? floatval($course['discount_price']) : null;
        $course['avg_rating'] = $course['avg_rating'] ? round(floatval($course['avg_rating']), 1) : 0;
        $course['enrollment_count'] = intval($course['enrollment_count']);
        $course['review_count'] = intval($course['review_count']);
        $course['duration_hours'] = intval($course['duration_hours']);
        $course['total_lessons'] = intval($course['total_lessons']);
    }
    
    jsonResponse([
        'success' => true,
        'data' => $courses,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => intval($total),
            'total_pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Course list error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Terjadi kesalahan sistem'], 500);
}