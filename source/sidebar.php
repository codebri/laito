<div class="sidebar">
    <div class="list-group">
        <?php foreach ($pages as $key => $page): ?>
            <a class="list-group-item <?php if ($route === $key): ?>active<?php endif; ?>" href="<?=$key?>.html"><?=ucfirst($page)?></a>
        <?php endforeach; ?>
    </div>
</div>