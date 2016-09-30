<div class="sidebar">
    <?php foreach ($pages as $module): ?>
        <div>
            <h6 class="menu-label">
                <?=$module['title']?>
            </h6>
            <ul class="list-unstyled">
                <?php foreach ($module['sections'] as $key => $page): ?>
                    <li>
                        <?php if ($module['title'] === 'Links'): ?>
                            <a href="<?=$key?>" target='_blank'>
                                <?=ucfirst($page)?>
                            </a>
                        <?php else: ?>
                            <a class="<?php if ($route === $key): ?>active<?php endif; ?>" href="<?=$key?>.html">
                                <?=ucfirst($page)?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>