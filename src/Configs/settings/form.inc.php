<?php

use MagicPro\Config\Config;

$settings = Config::get('albion')->settings;
?>
<h2>Настройки "Albion online"</h2>

<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <div role="tabpanel">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#discord" aria-controls="mercure" role="tab" data-toggle="tab">Discord</a></li>
                <li role="presentation">
                    <a href="#roles" aria-controls="roles" role="tab" data-toggle="tab">Роли</a></li>
            </ul>
            <form name="mainForm" method="post" action="">
                <div class="tab-content pt-10">
                    <div role="tabpanel" id="discord" class="tab-pane active form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Token:</label>
                            <div class="col-sm-9 text-left">
                                <input type="text" name="token" value="<?= $settings->token ?>" class="form-control" aria-describedby="tokenHelp">
                                <span id="tokenHelp" class="help-block">Токен <a href="https://discord.com/developers/applications">Discord бота</a></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Discord server ID:</label>
                            <div class="col-sm-9 text-left">
                                <input type="text" name="guildId" value="<?= $settings->guildId ?>" class="form-control" aria-describedby="guildIdHelp">
                                <span id="guildIdHelp" class="help-block">ID дискорд сервера, guild.id в понятиях discord api</a>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>

                    <div role="tabpanel" id="roles" class="tab-pane form-horizontal">
                        <div class="form-group">
                            <label class="col-md-3 control-label">ducklings:</label>
                            <div class="col-sm-9 text-left">
                                <input type="text" name="ducklingsRole" value="<?= $settings->ducklingsRole ?>" class="form-control" aria-describedby="ducklingsRoleHelp">
                                <span id="ducklingsRoleHelp" class="help-block">Утята - утята, регистрация на gudilap подтверждена в discord</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">albion-recruit</label>
                            <div class="col-sm-9 text-left">
                                <span class="help-block">
                                    <input type="text" name="recruitRole" value="<?= $settings->recruitRole ?>" class="form-control" aria-describedby="lbionRecruitRoleHelp">
                                <span id="lbionRecruitRoleHelp" class="help-block">albion-recruit - члены гильдии, не утята</span>
                                </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">albion-guild:</label>
                            <div class="col-sm-9 text-left">
                                <input type="text" name="guildRole" value="<?= $settings->guildRole ?>" class="form-control" aria-describedby="guildRoleHelp">
                                <span id="guildRoleHelp" class="help-block">albion-guild - члены гильдии, утята</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">albion-advanced:</label>
                            <div class="col-sm-9 text-left">
                                <input type="text" name="advancedRole" value="<?= $settings->advancedRole ?>" class="form-control" aria-describedby="advancedRoleHelp">
                                <span id="advancedRoleHelp" class="help-block">albion-advanced - гвардейцы, утята</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">albion-office</label>
                            <div class="col-sm-9 text-left">
                                <span class="help-block">
                                    <input type="text" name="officerRole" value="<?= $settings->officerRole ?>" class="form-control" aria-describedby="officerRoleHelp">
                                <span id="officerRoleHelp" class="help-block">albion-office - офицеры, утята</span>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
