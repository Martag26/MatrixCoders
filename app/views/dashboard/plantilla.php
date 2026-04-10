<?php
$templates = is_array($templates ?? null) ? $templates : [];
$templateTypes = array_values(array_unique(array_map(fn($item) => $item['tipo'], $templates)));
sort($templateTypes);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Plantillas') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <main class="main-dashboard">
        <div class="mc-container">
            <div class="contenedor-dashboard contenedor-dashboard-content">
                <?php require __DIR__ . '/../layout/sidebar.php'; ?>

                <section class="contenido-dashboard workspace-page-shell">
                    <div class="template-catalog-page">
                        <div class="template-catalog-head">
                            <div>
                                <span class="template-kicker">PLANTILLAS</span>
                                <h1>Plantillas</h1>
                            </div>
                            <a class="section-link" href="<?= BASE_URL ?>/index.php?url=dashboard">Volver al dashboard</a>
                        </div>

                        <div class="template-toolbar">
                            <label class="template-search">
                                <input type="search" id="templateSearch" placeholder="Buscar plantilla">
                            </label>
                            <label class="template-filter">
                                <select id="templateType">
                                    <option value="">Tipo de plantilla</option>
                                    <?php foreach ($templateTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>

                        <div class="template-section-head">
                            <h2>Favoritas</h2>
                        </div>

                        <div class="template-favorites-wrap" id="templateFavoritesWrap">
                            <div class="template-grid" id="templateFavoritesGrid"></div>
                            <p class="template-favorites-empty" id="templateFavoritesEmpty">Todavía no has marcado plantillas como favoritas.</p>
                        </div>

                        <div class="template-section-head">
                            <h2>Plantillas destacadas</h2>
                        </div>

                        <div class="template-grid" id="templateGrid">
                            <?php foreach ($templates as $template): ?>
                                <article
                                    class="template-gallery-card"
                                    data-template-id="<?= htmlspecialchars($template['id']) ?>"
                                    data-template-title="<?= htmlspecialchars(strtolower($template['titulo'])) ?>"
                                    data-template-type="<?= htmlspecialchars($template['tipo']) ?>"
                                    data-template-tags="<?= htmlspecialchars(strtolower(implode(' ', $template['etiquetas']))) ?>">
                                    <button class="template-favorite" type="button" aria-label="Destacar plantilla">☆</button>
                                    <div class="template-visual template-visual-<?= htmlspecialchars($template['icono']) ?>">
                                        <?php if ($template['icono'] === 'document'): ?>
                                            <div class="tpl-sheet">
                                                <span></span><span></span><span></span>
                                            </div>
                                        <?php elseif ($template['icono'] === 'code'): ?>
                                            <div class="tpl-code-block">
                                                <span></span><span></span><span></span><span></span>
                                            </div>
                                        <?php elseif ($template['icono'] === 'plan'): ?>
                                            <div class="tpl-plan">
                                                <span></span><span></span><span></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="tpl-brief">
                                                <span></span><span></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="template-gallery-body">
                                        <h3><?= htmlspecialchars($template['titulo']) ?></h3>
                                        <p><?= htmlspecialchars($template['descripcion']) ?></p>
                                        <div class="template-chip-row">
                                            <?php foreach ($template['etiquetas'] as $tag): ?>
                                                <span class="template-chip"><?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <a class="template-use-btn" href="<?= BASE_URL ?>/index.php?url=nuevo-documento&template=<?= urlencode($template['id']) ?>">
                                        Usar plantilla
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const search = document.getElementById('templateSearch');
            const type = document.getElementById('templateType');
            const cards = Array.from(document.querySelectorAll('.template-gallery-card'));
            const favoritesWrap = document.getElementById('templateFavoritesWrap');
            const favoritesGrid = document.getElementById('templateFavoritesGrid');
            const favoritesEmpty = document.getElementById('templateFavoritesEmpty');
            const favoriteStorageKey = 'matrixcoders_template_favorites';

            function normalize(value) {
                return (value || '').toLowerCase().trim();
            }

            function getFavorites() {
                try {
                    return JSON.parse(localStorage.getItem(favoriteStorageKey) || '[]');
                } catch (error) {
                    return [];
                }
            }

            function saveFavorites(ids) {
                localStorage.setItem(favoriteStorageKey, JSON.stringify(ids));
            }

            function renderFavoriteStates() {
                const favorites = getFavorites();
                cards.forEach((card) => {
                    const isFavorite = favorites.includes(card.dataset.templateId);
                    const button = card.querySelector('.template-favorite');
                    card.classList.toggle('is-favorite', isFavorite);
                    if (button) {
                        button.textContent = isFavorite ? '★' : '☆';
                    }
                });
            }

            function attachFavoriteEvents(scopeCards) {
                scopeCards.forEach((card) => {
                    const button = card.querySelector('.template-favorite');
                    if (!button) return;
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        const id = card.dataset.templateId;
                        const favorites = getFavorites();
                        const next = favorites.includes(id)
                            ? favorites.filter((item) => item !== id)
                            : [...favorites, id];
                        saveFavorites(next);
                        renderFavoriteStates();
                        renderFavorites();
                    });
                });
            }

            function renderFavorites() {
                const favorites = getFavorites();
                favoritesGrid.innerHTML = '';

                const favCards = cards.filter((card) => favorites.includes(card.dataset.templateId));
                favoritesEmpty.style.display = favCards.length ? 'none' : '';
                favoritesWrap.classList.toggle('has-favorites', favCards.length > 0);

                favCards.forEach((card) => {
                    const clone = card.cloneNode(true);
                    favoritesGrid.appendChild(clone);
                });

                attachFavoriteEvents(Array.from(favoritesGrid.querySelectorAll('.template-gallery-card')));
                renderFavoriteStates();
            }

            function filterCards() {
                const searchValue = normalize(search.value);
                const typeValue = type.value;

                cards.forEach((card) => {
                    const title = card.dataset.templateTitle || '';
                    const tags = card.dataset.templateTags || '';
                    const matchesSearch = searchValue === '' || title.includes(searchValue) || tags.includes(searchValue);
                    const matchesType = typeValue === '' || card.dataset.templateType === typeValue;
                    card.style.display = matchesSearch && matchesType ? '' : 'none';
                });
            }

            attachFavoriteEvents(cards);
            renderFavoriteStates();
            renderFavorites();
            search.addEventListener('input', filterCards);
            type.addEventListener('change', filterCards);
        })();
    </script>
</body>

</html>
