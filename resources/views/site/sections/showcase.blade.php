<!-- ============ DARK SHOWCASE ============ -->
    <section class="showcase" data-showcase>
        <?php foreach (($showcase_decor ?? []) as $d): ?><img class="showcase__decor" src="<?= $d['src'] ?>" alt="" style="<?= $d['style'] ?>"><?php endforeach; ?>
        <img class="showcase__bg" src="assets/icons/showcase-bg.svg" alt="">
        <div class="showcase__content">
            <div class="showcase__head">
                <h2 class="h2 h2--center h2--white"><?= $showcase_title ?? 'What Our Magazine Design Services Cover' ?></h2>
                <p class="showcase__lead">{!! $showcase_lead ?? $G('dark-showcase.1') !!}</p>
            </div>
            <div class="showcase__row" data-showcase-row>
                <?php foreach ($showcase_cards as $c): ?>
                    <article class="showcase-card">
                        <img class="showcase-card__img" src="<?= iu('assets/images/', $c['img']) ?>" alt="">
                        <span class="tag" style="background: <?= $c['tag_color'] ?>"><?= $c['tag'] ?></span>
                        <div class="showcase-card__body">
                            <div class="showcase-stats">
                                <?php $stats = !empty($c['stats']) ? array_values($c['stats']) : array_fill(0, 3, [18, $G('dark-showcase.2')]); ?>
                                <?php foreach ($stats as $i => $st): ?>
                                    <?php if ($i): ?><span class="showcase-stats__line"></span><?php endif; ?>
                                    <div class="showcase-stat"><span class="showcase-stat__value"><span data-count="<?= (int) ($st[0] ?? 0) ?>"><?= (int) ($st[0] ?? 0) ?></span>+</span><span class="showcase-stat__label"><?= $st[1] ?? '' ?></span></div>
                                <?php endforeach; ?>
                            </div>
                            <hr class="h-line h-line--light">
                            <div class="showcase-card__text">
                                <div>
                                    <h3 class="h-card h-card--white">{!! !empty($c['title']) ? $c['title'] : $G('dark-showcase.3') !!}</h3>
                                    <p>{!! !empty($c['text']) ? $c['text'] : $G('dark-showcase.4') !!}</p>
                                </div>
                                <a href="{{ !empty($c['href']) ? $c['href'] : $G('showcase.2', 'contact-us.php') }}" class="text-link text-link--filled">{{ $G('showcase.1', 'View Case Study') }}<img src="assets/icons/arrow-link.svg" width="24" height="24" alt=""></a>
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


