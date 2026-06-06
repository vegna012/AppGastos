<?php if (!empty($success)): ?>
    <div class="alert alert-success" role="alert">
        <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($accessError)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars((string) $accessError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($warning)): ?>
    <div class="alert alert-warning" role="alert">
        <?= htmlspecialchars((string) $warning, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($info)): ?>
    <div class="alert alert-info" role="alert">
        <?= htmlspecialchars((string) $info, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $alertError): ?>
                <li><?= htmlspecialchars((string) $alertError, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($rejectErrors)): ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($rejectErrors as $alertError): ?>
                <li><?= htmlspecialchars((string) $alertError, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
