<div>
    <div class="col-md-12">
        <div class="panel  panel-danger">
            <div class="panel-body">
                {if $installationCheck != true}
                    <div class="alert alert-danger">Moduł nie jest poprawnie zainstalowany.</div>
                    <br/><a class="btn btn-danger" href="addonmodules.php?module=domenytv&action=installer_install">Zainstaluj ponownie</a>
                {/if}

                {if $installationCheck == true}
                    <div class="alert alert-success">Moduł został poprawnie zainstalowany.</div>
                    <br/><a class="btn btn-info" href="addonmodules.php?module=domenytv">Wróć do strony głównej modułu</a>
                {/if}

            </div>
        </div>
    </div>
</div>