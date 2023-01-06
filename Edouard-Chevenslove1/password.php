<form method="post" name="update_password" id="update_password">
    <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Changer votre mot de passe</h5>
    <div id="login-feedback" class='alert alert-danger'></div>
    <div class="form-outline mb-4">
    <input required name="password" type="password" id="password" class="form-control form-control-lg" />
    <label class="form-label" for="password">Nouveau mot de passe</label>
    </div>
    <div class="form-outline mb-4">
    <input required name="confirm_password" type="password" id="confirm_password" class="form-control form-control-lg" />
    <label class="form-label" for="confirm_password">Confirmer le mot de passe</label>
    </div>
    <div class="pt-1 mb-4">
    <button onclick="update_password_()" class="btn btn-dark btn-lg btn-block" type="button">Valider</button>
    </div>
</form>