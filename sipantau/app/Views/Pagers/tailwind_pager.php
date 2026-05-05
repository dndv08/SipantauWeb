<?php if ($pager->getPageCount('default') > 1): ?>
<nav aria-label="Page navigation" class="flex justify-center mt-6">
    <ul class="flex items-center space-x-2">
        <!-- Tombol Previous -->
        <?php if ($pager->hasPreviousPage('default')): ?>
            <li>
                <a href="<?= $pager->getPreviousPage('default') ?>"
                    class="flex items-center justify-center w-10 h-10 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition"
                    aria-label="Previous">
                    &laquo;
                </a>
            </li>
        <?php else: ?>
            <li>
                <span
                    class="flex items-center justify-center w-10 h-10 text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    &laquo;
                </span>
            </li>
        <?php endif; ?>

        <!-- Nomor Halaman -->
        <?php foreach ($pager->links('default') as $link): ?>
            <li>
                <?php if ($link['active']): ?>
                    <span
                        class="flex items-center justify-center w-10 h-10 text-white bg-blue-500 border border-blue-500 rounded-lg font-semibold shadow-md transform scale-105 transition hover:bg-blue-600">
                        <?= $link['title'] ?>
                    </span>
                <?php else: ?>
                    <a href="<?= $link['uri'] ?>"
                        class="flex items-center justify-center w-10 h-10 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                        <?= $link['title'] ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

        <!-- Tombol Next -->
        <?php if ($pager->hasNextPage('default')): ?>
            <li>
                <a href="<?= $pager->getNextPage('default') ?>"
                    class="flex items-center justify-center w-10 h-10 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition"
                    aria-label="Next">
                    &raquo;
                </a>
            </li>
        <?php else: ?>
            <li>
                <span
                    class="flex items-center justify-center w-10 h-10 text-gray-300 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    &raquo;
                </span>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
