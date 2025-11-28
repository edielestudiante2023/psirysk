<div class="pdf-footer">
    <?php if (isset($pageNumber)): ?>
    <div class="pdf-footer-page">
        Página <?= $pageNumber ?><?= isset($totalPages) ? ' de ' . $totalPages : '' ?>
    </div>
    <?php endif; ?>
</div>
