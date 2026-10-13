@php
$ti = 'assets/images/ppt/';          // page images
$tc = 'assets/icons/ppt/';           // page icons
$pi = 'assets/images/prepress/';     // stats photo (same Figma image as the Pre-Press page)
$pc = 'assets/icons/prepress/';      // why-card icons (same card design as the Pre-Press page)
$mc = 'assets/icons/magazine/';      // shared tool glyphs
$vi = 'assets/images/video/';        // shared offer art
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ FAQs ============ -->
    <section class="container ve-faq">
        <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
        <div class="acc acc--faq" data-accordion>
            <?php foreach ($ppt_faqs as $i => $f): ?>
                <div class="acc-item <?= $i === 0 ? 'is-open' : '' ?>">
                    <button type="button" class="acc-head" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                        <span class="acc-chevron"><img src="<?= $vc ?>faq-chevron.svg" width="20" height="20" alt=""></span>
                        <span class="acc-title"><?= $f[0] ?></span>
                    </button>
                    <div class="acc-panel"><div class="acc-panel__inner"><p><?= $f[1] ?></p></div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
