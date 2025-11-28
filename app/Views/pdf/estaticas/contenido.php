<div class="pdf-page">
    <?= view('pdf/_partials/header', ['company' => $company, 'consultant' => $consultant]) ?>

    <div class="pdf-content">
        <?php if ($isFirstPage): ?>
        <h1 class="section-title" style="border-bottom: none; color: #0066cc;">Contenido</h1>
        <?php endif; ?>

        <div class="toc-container">
            <?php foreach ($items as $item): ?>
            <div class="toc-item level-<?= $item['level'] ?>">
                <span class="toc-title"><?= esc($item['title']) ?></span>
                <span class="toc-dots"></span>
                <span class="toc-page"><?= $item['page'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?= view('pdf/_partials/footer') ?>
</div>
