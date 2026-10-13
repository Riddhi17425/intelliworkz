<!-- ============ ENGAGEMENT MODEL ============ -->
    <section class="container engagement">
        <div class="section-head">
            <h2 class="h2 h2--center">{!! $G('engagement-model.2') !!}</h2>
            <p class="lead lead--engagement">{!! $G('engagement-model.3') !!}</p>
        </div>

        <div class="models">
            <?php foreach ($engagement_models as $m): ?>
                <article class="model model--<?= $m['height'] ?>">
                    <div class="model__panel <?= $m['panel'] ?>">
                        <img class="model__img <?= $m['img_class'] ?? '' ?>" src="<?= iu('assets/images/', $m['img']) ?>" alt="">
                        <span class="model__no" style="color: <?= $m['num_color'] ?>"><?= $m['no'] ?></span>
                        <div class="model__title">
                            <h3 class="h-card"><?= $m['title'] ?></h3>
                            <span class="model__tag" style="background: <?= $m['color'] ?>"><?= $m['tag'] ?></span>
                        </div>
                    </div>
                    <ul class="model__list">
                        <?php foreach ($m['bullets'] as $b): ?>
                            <li><span class="dot-anchor"></span><p class="small"><?= $b ?></p></li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="cta-band cta-band--pricing">
            <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
            <div class="price-badge">
                @include('site.sections.price-badge')
            </div>
            <div class="cta-band__copy">
                <h2 class="cta-title">{!! $G('engagement-model.4') !!}</h2>
                <p>{!! $G('engagement-model.5') !!}</p>
                <?php if ($note = $T('what-we-offer-cta-engagement.8', '')): ?><p>{!! $note !!}</p><?php endif; ?>
            </div>
            <?php btn($G('engagement-model.1'), 'btn--cta', 'arrow-cta.svg', $G('engagement-model.1.url', 'contact-us.php')); ?>
        </div>
    </section>


