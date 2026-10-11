            </main>

            <!-- Bottom Footer -->
            <footer class="app-footer">
                <div class="footer-content">
                    <p>&copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. Built with PHP, MySQL, HTML, CSS & Vanilla JS.</p>
                    <p class="footer-env">
                        <span class="badge badge-outline">PHP <?= PHP_VERSION ?></span>
                        <span class="badge badge-outline">Ready for Vercel & Railway</span>
                    </p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global JavaScript Bundle -->
    <script src="<?= url('assets/js/main.js') ?>"></script>
    <?php if (isset($extraJs)): ?>
        <script src="<?= url('assets/js/' . $extraJs) ?>"></script>
    <?php endif; ?>
</body>
</html>
