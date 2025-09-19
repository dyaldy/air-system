<!-- Main Content Card -->
<div class="card mx-auto rounded-5 shadow border-0 mb-5" style="margin-top: 5rem; max-width: 95%;">
    <!-- Card Header with Title -->
    <div class="card-header bg-white border-bottom px-lg-5 px-4 py-4">
        <h3 class="m-0"><?= htmlspecialchars($title ?? 'Type Form', ENT_QUOTES, 'UTF-8') ?></h3>
    </div>

    <!-- Card Body with Form -->
    <div class="card-body px-lg-5 px-4 py-4">
        <?php if (!empty($this->session->flashdata('message'))):
            $msg = $this->session->flashdata('message');
            // expected format: [level, text]
            if (is_array($msg) && count($msg) === 2): ?>
                <div class="alert alert-<?= htmlspecialchars($msg[0]) ?>"><?= $msg[1] ?></div>
        <?php endif;
        endif; ?>

        <?php if (function_exists('validation_errors') && validation_errors()): ?>
            <div class="alert alert-danger"><?= validation_errors(); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <?= form_open_multipart($form_action, ['id' => 'typeForm']); ?>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <input type="text" name="type" id="type" class="form-control" maxlength="10" required value="<?= isset($type['type']) ? htmlspecialchars($type['type'], ENT_QUOTES, 'UTF-8') : set_value('type') ?>">
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar (opsional)</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <div id="previewBox" class="mt-3">
                        <?php if (!empty($type['image'])): ?>
                            <img id="previewImg" src="<?= htmlspecialchars(base_url('assets/img/pneumatic_types/' . $type['image']), ENT_QUOTES, 'UTF-8') ?>" style="max-width:240px; display:block;">
                        <?php else: ?>
                            <img id="previewImg" src="<?= base_url('assets/img/pneumatic-default.jpg') ?>" style="max-width:240px; display:block;">
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Simpan</button>
                    <a href="<?= site_url('pneumatic_type') ?>" class="btn btn-secondary rounded-pill px-4">Batal</a>
                </div>

                <?= form_close(); ?>
            </div>

            <div class="col-md-6">
                <div class="card bg-light h-100">
                    <div class="card-body">
                        <h5 class="card-title">Preview</h5>
                        <p class="text-muted">Preview gambar yang akan disimpan untuk type.</p>
                        <div class="border p-3 text-center">
                            <img id="previewImgCard" src="<?= !empty($type['image']) ? htmlspecialchars(base_url('assets/img/pneumatic_types/' . $type['image']), ENT_QUOTES, 'UTF-8') : base_url('assets/img/pneumatic-default.jpg') ?>" alt="Preview" style="max-width:100%; height:180px; object-fit:contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('image').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(ev) {
            var src = ev.target.result;
            var img = document.getElementById('previewImg');
            var imgCard = document.getElementById('previewImgCard');
            if (img) img.src = src;
            if (imgCard) imgCard.src = src;
        };
        reader.readAsDataURL(file);
    });
</script>