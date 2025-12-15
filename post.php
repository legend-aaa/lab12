<?php
require_once __DIR__ . '/includes/functions.php';

$postId = $_GET['id'] ?? '';
if (empty($postId)) {
    header('Location: index.php');
    exit;
}

$post = getPostById($postId);
if (!$post) {
    header('Location: index.php');
    exit;
}

$allComments = loadData('comments.json');
$postComments = array_filter($allComments, function($comment) use ($postId) {
    return $comment['post_id'] === $postId;
});

usort($postComments, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Только авторизованные пользователи могут оставлять комментарии';
    } else {
        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            $error = 'Комментарий не может быть пустым';
        } elseif (strlen($content) < 3) {
            $error = 'Комментарий должен содержать минимум 3 символа';
        } else {
            $newComment = [
                'id' => uniqid('comment_', true),
                'post_id' => $postId,
                'author_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $allComments[] = $newComment;
            
            if (saveData('comments.json', $allComments)) {
                header('Location: post.php?id=' . $postId);
                exit;
            } else {
                $error = 'Ошибка при сохранении комментария';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой блог - <?= $post['title'] ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Просмотр записи</h1>
            <nav class="nav">
                <a href="index.php">Главная</a>
                <a href="create.php">Создать пост</a>
                <a href="logout.php">Выход (<?= $_SESSION['username'] ?? 'Гость' ?>)</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <article class="post-detail">
            <h2><?= $post['title'] ?></h2>
            <div class="post-meta">
                Автор: <?= $post['username'] ?? 'Неизвестен' ?>
                | <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>
            </div>
            
            <?php if (!empty($post['media']) && is_array($post['media'])): ?>
                <div class="post-media">
                    <?php foreach ($post['media'] as $mediaPath): ?>
                        <img src="<?= $mediaPath ?>" alt="Медиа файл">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="post-content">
                <?= $post['content'] ?>
            </div>
        </article>

        <section class="comments-section">
            <h3>Комментарии (<?= count($postComments) ?>)</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="" class="comment-form">
                    <div class="form-group">
                        <label for="comment-content">Ваш комментарий:</label>
                        <textarea id="comment-content" name="content" required minlength="3" placeholder="Введите ваш комментарий..."><?= $_POST['content'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить комментарий</button>
                </form>
            <?php else: ?>
                <p class="empty-state">
                    Только <a href="login.php">авторизованные пользователи</a> могут оставлять комментарии.
                </p>
            <?php endif; ?>
            
            <?php if (empty($postComments)): ?>
                <p class="empty-state">Пока нет комментариев. Будьте первым!</p>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($postComments as $comment): ?>
                        <div class="comment">
                            <div class="comment-author">
                                <?= $comment['username'] ?>
                            </div>
                            <div class="comment-date">
                                <?= date('d.m.Y в H:i', strtotime($comment['created_at'])) ?>
                            </div>
                            <div class="comment-content">
                                <?= $comment['content'] ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>Мой блог © 2024 - Практический проект на PHP</p>
        </div>
    </footer>
</body>
</html>