<?php
use App\Helpers\Url;
require dirname(__DIR__) . '/layouts/header.php';

$hasData = $stats['tempo_estudado'] > 0 || $stats['tarefas_concluidas'] > 0;
?>
<div class="app-shell">
    <?php require dirname(__DIR__) . '/layouts/sidebar.php'; ?>
    <div class="app-content">
        <?php require dirname(__DIR__) . '/layouts/navbar.php'; ?>
        <main class="page-content">
            <section class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <p class="eyebrow">Produtividade</p>
                    <h1>Estatísticas</h1>
                    <p>Acompanhe seu desempenho e evolução nos estudos.</p>
                </div>
                
                <form class="d-flex gap-2" method="GET" action="<?= Url::to('/estatisticas') ?>">
                    <select name="disciplina" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas as disciplinas</option>
                        <?php foreach ($disciplines as $discipline): ?>
                            <option value="<?= htmlspecialchars((string) $discipline['id'], ENT_QUOTES, 'UTF-8') ?>" <?= $currentDiscipline === $discipline['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $discipline['nome'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="dia" <?= $currentFilter === 'dia' ? 'selected' : '' ?>>Hoje</option>
                        <option value="semana" <?= $currentFilter === 'semana' ? 'selected' : '' ?>>Esta Semana</option>
                        <option value="mes" <?= $currentFilter === 'mes' ? 'selected' : '' ?>>Este Mês</option>
                    </select>
                </form>
            </section>

            <?php if (!$hasData): ?>
                <div class="alert alert-info mt-4" role="alert">
                    <h4 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Sem dados suficientes</h4>
                    <p>Ainda não há registros suficientes para o período e/ou disciplina selecionados. Complete tarefas e registre sessões de estudo para ver suas estatísticas aqui.</p>
                </div>
            <?php else: ?>
                <div class="row g-4 mt-2">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Tempo Estudado</h6>
                                <h2 class="mb-0">
                                    <?php
                                        $minutes = $stats['tempo_estudado'];
                                        if ($minutes < 60) echo $minutes . ' min';
                                        else echo intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'min';
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Tarefas Concluídas</h6>
                                <h2 class="mb-0"><?= $stats['tarefas_concluidas'] ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Sessões de Estudo</h6>
                                <h2 class="mb-0"><?= $stats['sessoes_estudo'] ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">Produtividade</h6>
                                <h2 class="mb-0 text-primary"><?= $stats['produtividade'] ?> pts</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Evolução do Tempo de Estudo</h5>
                                <div style="height: 300px;">
                                    <canvas id="evolutionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/mobile-navigation.php'; ?>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>

<?php if ($hasData): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawData = <?= json_encode($stats['grafico']) ?>;
    
    const labels = rawData.map(item => {
        // Format label depending on currentFilter (hour vs date)
        if ('<?= $currentFilter ?>' === 'dia') {
            return item.label + 'h';
        } else {
            const dateParts = item.label.split('-');
            return dateParts.length === 3 ? `${dateParts[2]}/${dateParts[1]}` : item.label;
        }
    });
    const values = rawData.map(item => parseInt(item.valor, 10));

    const ctx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Minutos Estudados',
                data: values,
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Minutos'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
<?php endif; ?>
