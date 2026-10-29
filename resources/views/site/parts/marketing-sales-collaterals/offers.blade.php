    <!-- ============ WHAT WE OFFER (related services) + CTA + ENGAGEMENT ============ -->
    <div class="ve-offer-engagement">
        <section class="ve-offers cl-related">
            <div class="ve-offers__head">
                <h2 class="h2 h2--center">{!! $T('what-we-offer-cta-engagement.2') !!}</h2>
                <p>{!! $T('what-we-offer-cta-engagement.3') !!}</p>
            </div>
            <div class="container cl-related__grid">
                <?php foreach ($cl_related as $k => $o): ?>
                    <article class="cl-related__card">
                        <h3><?= $o['title'] ?></h3>
                        <p><?= $o['text'] ?></p>
                        <?php if ($T('what-we-offer-cta-engagement.7')): ?>
                            <a class="cl-related__link" href="<?= e(!empty($o['href']) ? $o['href'] : service_href($o['title'] ?? '', '') !== 'contact-us.php' ? service_href($o['title'] ?? '', '') : 'services.php') ?>"><?= $T('what-we-offer-cta-engagement.7') ?><span class="cl-related__arrow" aria-hidden="true"></span></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="cta-band cta-band--rich">
            <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
            <div class="cta-band__copy">
                <?php if ($T('what-we-offer-cta-engagement.5')): ?><p class="cta-band__eyebrow">{!! $T('what-we-offer-cta-engagement.5') !!}</p><?php endif; ?>
                <h2 class="cta-title">{!! $T('what-we-offer-cta-engagement.4') !!}</h2>
                <?php if ($T('what-we-offer-cta-engagement.6')): ?><p>{!! $T('what-we-offer-cta-engagement.6') !!}</p><?php endif; ?>
            </div>
            <?php btn($T('what-we-offer-cta-engagement.1'), 'btn--cta', 'arrow-cta.svg', $T('what-we-offer-cta-engagement.1.url', 'contact-us.php')); ?>
        </section>

        @include('site.sections.engagement')
    </div>
