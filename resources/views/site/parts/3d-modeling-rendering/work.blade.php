@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
@endphp
@once
@push('scripts')
<script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@3.5.0/dist/model-viewer.min.js"></script>
@endpush
@endonce
    <!-- ============ 360 VIEWER + SELECTED WORK ============ -->
    <!-- Figma keeps the heading, filter tabs, gallery strip, the "Flexibel" panel and the
         Exploded View button all inside ONE 1920x920 scene (node 6:179), overlaid on the same
         grid-floor photo, not stacked as separate blocks. Every left/top below is that node's
         exact coordinate. -->
    <section class="td-viewer">
        <img class="td-viewer__bg" src="{{ $T('360-viewer-selected-work.1') }}" alt="">
        <img class="td-viewer__texture" src="{{ $T('360-viewer-selected-work.2') }}" alt="">

        <h2 class="h2 h2--center td-viewer__heading">{!! $T('360-viewer-selected-work.3') !!}</h2>
        <?php if ($T('filter-tabs.1', '1') !== '0' && !empty($td_filters)): ?>
            <div class="filter-tabs td-viewer__tabs">
            <?php foreach ($td_filters as $key => $label): ?>
                <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
            <?php endif; ?>

        <div class="td-rings">
            <span class="td-rings__ellipse td-rings__ellipse--shadow"></span>
            <span class="td-rings__ellipse td-rings__ellipse--outer-dash"></span>
            <span class="td-rings__ellipse td-rings__ellipse--outer"></span>
            <span class="td-rings__ellipse td-rings__ellipse--mid"></span>
            <span class="td-rings__ellipse td-rings__ellipse--inner"></span>
            <span class="td-rings__dot"></span>
            <!-- Real, drag-to-rotate 3D model (glTF/.glb via <model-viewer>). Loads assembled by
                 default; the Exploded View button below swaps the actual .glb src to the "-open"
                 file, not a camera trick. -->
            <model-viewer
                class="td-rings__product"
                src="<?= $tm ?>expansion-joint.glb"
                poster="<?= $ti ?>product.png"
                alt="Belt type fabric expansion joint — 3D model"
                camera-controls
                disable-pan
                touch-action="pan-y"
                interaction-prompt="none"
                shadow-intensity="0"
                exposure="1.2"
                auto-rotate
                rotation-per-second="18deg"
                camera-orbit="25deg 75deg 85%"
                field-of-view="30deg"
                min-camera-orbit="auto 55deg auto"
                max-camera-orbit="auto 100deg auto"
                min-field-of-view="20deg"
                max-field-of-view="45deg"
                data-src-normal="<?= $tm ?>expansion-joint.glb"
                data-src-exploded="<?= $tm ?>expansion-joint-open.glb"
                reveal="auto"
            ></model-viewer>
            <img class="td-rings__cursor" src="<?= $tc ?>rotate-cursor.svg" alt="">
        </div>
        <div class="td-cue">
            <strong>360°</strong><span>{!! $T('360-viewer-selected-work.4') !!}</span>
        </div>

        <div class="td-viewer__panel">
            <h3>{!! $T('360-viewer-selected-work.5') !!}</h3> <!-- kept exactly as written in Figma; looks like a typo for "Flexible" -->
            <p class="td-viewer__panel-sub">{!! $T('360-viewer-selected-work.6') !!}</p>
            <p class="td-viewer__panel-text">{!! $T('360-viewer-selected-work.7') !!}</p>
        </div>
        <button type="button" class="td-viewer__btn" data-explode-toggle aria-pressed="false" data-label-exploded="{{ strip_tags($T('360-viewer-selected-work.8')) }}" data-label-assembled="{{ strip_tags($T('360-viewer-selected-work.9')) }}" data-label-loading="{{ strip_tags($T('360-viewer-selected-work.10')) }}">
            <span data-explode-label>{!! $T('360-viewer-selected-work.8') !!}</span>
            <span class="td-viewer__btn-icon"><img src="<?= $tc ?>exploded-view.svg" width="20" height="20" alt=""></span>
        </button>

        <div class="td-gallery">
            <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
            <div class="td-gallery__viewport" data-gallery>
                <div class="td-gallery__track" data-gallery-track>
                    <?php foreach ($td_gallery as $i => $g): ?>
                        <button type="button" class="td-gallery__tile <?= $i === 5 ? 'is-featured' : '' ?>" data-cats="<?= $g['cats'] ?>">
                            <img src="<?= iu($ti, $g['img']) ?>" alt="3D render example">
                            <span class="td-gallery__badge"><?= sprintf('%02d', $i + 1) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
        </div>
    </section>
