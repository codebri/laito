<div class="sidebar">
    <div class="list-group">
        <?php foreach ($pages as $page): ?>
            <a class="list-group-item <?php if ($route === $page): ?>active<?php endif; ?>" href="<?=$page?>.html"><?=ucfirst($page)?></a>
        <?php endforeach; ?>
    </div>
</div>