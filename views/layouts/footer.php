<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.APP_CONFIG = window.APP_CONFIG || {}; window.APP_CONFIG.pomodoroUrl = <?= json_encode(\App\Helpers\Url::to('/pomodoro'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="<?= \App\Helpers\Url::asset('assets/js/app.js') ?>"></script>
<script src="<?= \App\Helpers\Url::asset('assets/js/pomodoro-timer.js') ?>"></script>
</body>
</html>
