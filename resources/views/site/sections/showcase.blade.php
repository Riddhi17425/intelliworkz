<!-- ============ DARK SHOWCASE ============ -->
    <section class="showcase" data-showcase>
        <?php foreach (($showcase_decor ?? []) as $d): ?><img class="showcase__decor" src="<?= $d['src'] ?>" alt="" style="<?= $d['style'] ?>"><?php endforeach; ?>
        <img class="showcase__bg" src="assets/icons/showcase-bg.svg" alt="">
        <div class="showcase__content">
            <div class="showcase__head">
                <h2 class="h2 h2--center h2--white"><?= $showcase_title ?? 'What Our Magazine Design Services Cover' ?></h2>
                <p class="showcase__lead">{!! $G('dark-showcase.1') !!}</p>
            </div>
            <div class="showcase__row" data-showcase-row>
                <?php foreach ($showcase_cards as $c): ?>
                    <article class="showcase-card">
                        <img class="showcase-card__img" src="<?= iu('assets/images/', $c['img']) ?>" alt="">
                        <span class="tag" style="background: <?= $c['tag_color'] ?>"><?= $c['tag'] ?></span>
                        <div class="showcase-card__body">
                            <div class="showcase-stats">
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <?php if ($i): ?><span class="showcase-stats__line"></span><?php endif; ?>
                                    <div class="showcase-stat"><span class="showcase-stat__value"><span data-count="18">18</span>+</span><span class="showcase-stat__label">{!! $G('dark-showcase.2') !!}</span></div>
                                <?php endfor; ?>
                            </div>
                            <hr class="h-line h-line--light">
                            <div class="showcase-card__text">
                                <div>
                                    <h3 class="h-card h-card--white">{!! $G('dark-showcase.3') !!}</h3>
                                    <p>{!! $G('dark-showcase.4') !!}</p>
                                </div>
                                <a href="#" class="text-link text-link--filled">View Case Study<img src="assets/icons/arrow-link.svg" width="24" height="24" alt=""></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="dots dots--light dots--center" data-showcase-dots>
                <?php foreach ($showcase_cards as $i => $c): ?><button type="button" class="dots__dot" aria-label="Show slide <?= $i + 1 ?>"></button><?php endforeach; ?>
            </div>
        </div>
    </section>


