<?php

/**
 * bs_full.php - - Bootstrap 4.5.2 Pager Template.
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */


$pager->setSurroundCount(2);
?>
<div class="w-100 d-flex justify-content-center">
<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination">
        <?php if ($pager->hasPreviousPage()) : ?>
            <!-- <li class="page-item">
                <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true"><?= lang('Pager.first') ?></span>
                </a>
            </li> -->
            <li class="page-item">
                <a class="page-link shadow" href="<?= $pager->getPreviousPage() ?>" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true"> < </span>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li <?= $link['active']  ? 'class="page-item active"' : 'page-item' ?>>
                <a class="page-link shadow" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNextPage()) : ?>
            <li class="page-item">
                <a class="page-link shadow" href="<?= $pager->getNextPage() ?>" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true"> > </span>
                </a>
            </li>
            <!-- <li class="page-item">
                <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true"><?= lang('Pager.last') ?></span>
                </a>
            </li> -->
        <?php endif ?>
    </ul>
</nav> 
</div> 


