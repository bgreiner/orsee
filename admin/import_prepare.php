<?php
// part of orsee. see orsee.org
ob_start();
$old_versions=array('orsee2'=>'versions <3.0');
$title="prepare_data_import";
$menu__area="options";
include("header.php");

if ($proceed) {
    check_allow('import_data','options_main.php');
}
if ($proceed) {
    $continue=true;
    if ((isset($_REQUEST['submit1']) && $_REQUEST['submit1']) || (isset($_REQUEST['submit2']) && $_REQUEST['submit2'])) {
        if (!csrf__validate_request_message()) {
            $proceed=false;
            $continue=false;
        }
    }
}

if ($proceed) {
    $continue=true;


    if ($continue) {
        $databases=array();
        $query="SELECT `SCHEMA_NAME`
                FROM `INFORMATION_SCHEMA`.`SCHEMATA`
                WHERE `SCHEMA_NAME` NOT IN ('information_schema','mysql')";
        $result=or_query($query);
        while ($line=pdo_fetch_assoc($result)) {
            if ($line['SCHEMA_NAME']!=$site__database_database) {
                $databases[]=$line['SCHEMA_NAME'];
            }
        }

        // first step:
        if (!isset($_REQUEST['old_version']) || !isset($old_versions[$_REQUEST['old_version']])
            || !isset($_REQUEST['old_database']) || !in_array($_REQUEST['old_database'],$databases)) {
            $continue=false;

            echo '<form action="'.thisdoc().'" method="POST">
                    '.csrf__field().'
                    <div class="orsee-panel">
                        <div class="orsee-form-shell">';

            echo '          <div class="field">
                            <label class="label">From which ORSEE version do you want to import data?</label>
                            <div class="control"><span class="select is-primary"><SELECT name="old_version">';
            foreach ($old_versions as $ov=>$text) {
                echo '<OPTION value="'.$ov.'">'.$text.'</OPTION>';
            }
            echo '              </SELECT></span></div>
                        </div>';

            echo '          <div class="field">
                            <label class="label">From which database should the data be imported?</label>
                            <div class="control"><span class="select is-primary"><SELECT name="old_database">';
            foreach ($databases as $db) {
                echo '<OPTION value="'.$db.'">'.$db.'</OPTION>';
            }
            echo '              </SELECT></span></div>
                        </div>';

            echo '          <div class="field orsee-form-actions has-text-centered">
                                <INPUT type="submit" class="button orsee-btn" name="submit1" value="Next">
                            </div>
                        </div>
                    </div>
                  </form>';
        }
    }

    if ($continue) {
        $old_version=$_REQUEST['old_version'];
        $old_database=$_REQUEST['old_database'];


        /////////////////////////
        if ($old_version=="orsee2") {
            if (isset($_REQUEST['submit1'])) {
                echo '<FORM action="'.thisdoc().'" method="POST">'.csrf__field();
                echo '<INPUT type="hidden" name="old_version" value="'.$old_version.'">';
                echo '<INPUT type="hidden" name="old_database" value="'.$old_database.'">';
                echo '<div class="orsee-panel"><div class="orsee-form-shell">';

                echo '<div class="field">
                            <div class="control">Do you want to import
                            <UL><LI><B>all data</B> (admins, admin types, experiment types, faqs, language items, subpools, files, plus all what\'s listed below)</LI>
                                <LI>or only <B>participation data</B> (participants, experiments, sessions, participations, lab bookings, admin log, cron log, participant log)?</LI>
                            </UL>
                            </div>
                        </div>';
                echo '<div class="field has-text-centered"><div class="control"><span class="select is-primary"><SELECT name="import_type">
                            <OPTION value="all">all data</OPTION>
                            <OPTION value="participation">participation data</OPTION>
                            </SELECT></span></div></div>';
                echo '<div class="field"><hr class="orsee-option-divider"></div>';

                $statuses=participant_status__get_statuses();
                echo '<div class="field"><div class="control">There are '.count($statuses).' participant statuses defined in this system.<BR>
                            Please select how previous participant properties should be assigned to these states.
                            </div></div>';
                echo '<div class="field"><div class="orsee-table orsee-table-tablet-2cols orsee-table-mobile">';
                echo '<div class="orsee-table-row orsee-table-head"><div class="orsee-table-cell">Previous participant status</div><div class="orsee-table-cell">Map to current status</div></div>';
                $prev_statuses=array('n_n'=>array('not deleted, not excluded',1),
                                    'y_n'=>array('deleted, not excluded',2),
                                    'y_y'=>array('deleted and excluded',3));
                foreach ($prev_statuses as $ps=>$arr) {
                    echo '<div class="orsee-table-row"><div class="orsee-table-cell" data-label="Previous participant status">'.$arr[0].'</div>
                            <div class="orsee-table-cell" data-label="Map to current status"><span class="select is-primary"><SELECT name="status_'.$ps.'">';
                    foreach ($statuses as $status) {
                        echo '<OPTION value="'.$status['status_id'].'"';
                        if ($status['status_id']==$arr[1]) {
                            echo ' SELECTED';
                        }
                        echo '>'.$status['name'].'</OPTION>';
                    }
                    echo '</SELECT></span></div></div>';
                }
                echo '</div></div>';
                echo '<div class="field"><hr class="orsee-option-divider"></div>';

                $pstatuses=expregister__get_participation_statuses();
                echo '<div class="field"><div class="control">There are '.count($pstatuses).' experiment participation statuses defined in this system.<BR>
                            Please select how previous participation states should be assigned to these states.
                         </div></div>';
                echo '<div class="field"><div class="orsee-table orsee-table-tablet-2cols orsee-table-mobile">';
                echo '<div class="orsee-table-row orsee-table-head"><div class="orsee-table-cell">Previous participation state</div><div class="orsee-table-cell">Map to current participation status</div></div>';
                $prev_pstatuses=array('n_n'=>array('not shownup, not participated',3),
                                    'y_n'=>array('shownup, not participated',2),
                                    'y_y'=>array('shownup and participated',1));
                foreach ($prev_pstatuses as $ps=>$arr) {
                    echo '<div class="orsee-table-row"><div class="orsee-table-cell" data-label="Previous participation state">'.$arr[0].'</div>
                            <div class="orsee-table-cell" data-label="Map to current participation status"><span class="select is-primary"><SELECT name="pstatus_'.$ps.'">';
                    foreach ($pstatuses as $status) {
                        echo '<OPTION value="'.$status['pstatus_id'].'"';
                        if ($status['pstatus_id']==$arr[1]) {
                            echo ' SELECTED';
                        }
                        echo '>'.$status['internal_name'].'</OPTION>';
                    }
                    echo '</SELECT></span></div></div>';
                }
                echo '</div></div>';
                echo '<div class="field"><hr class="orsee-option-divider"></div>';

                $old_fields=array();
                $pars=array(':dbname'=>$old_database);
                $query="SELECT `COLUMN_NAME`
                FROM `INFORMATION_SCHEMA`.`COLUMNS`
                WHERE `TABLE_SCHEMA`= :dbname
                AND `TABLE_NAME`='or_participants'";
                $result=or_query($query,$pars);
                while ($line=pdo_fetch_assoc($result)) {
                    $old_fields[]=$line['COLUMN_NAME'];
                }
                $unset_in_old=array('participant_id','participant_id_crypt','creation_time','subpool_id',
                    'number_reg','number_noshowup','language','remarks','rules_signed','deleted','excluded','subscriptions');
                $old_fields=or_array_delete_values($old_fields,$unset_in_old);
                $old_has_email=in_array('email',$old_fields,true);
                if ($old_has_email) {
                    $old_fields=or_array_delete_values($old_fields,array('email'));
                }

                $new_fields=participant__userdefined_columns();
                if (!$old_has_email) {
                    $new_fields['email']=array('Type'=>'-','fieldtype'=>'email');
                }
                $query="SELECT * FROM ".table('profile_fields');
                $result=or_query($query);
                while ($line=pdo_fetch_assoc($result)) {
                    if (isset($new_fields[$line['mysql_column_name']])) {
                        $new_fields[$line['mysql_column_name']]['fieldtype']=$line['type'];
                    }
                }

                echo '<div class="field"><div class="control">
                        The following profile fields are defined in the current (new) system.
                        Please select from which column in the old system the data should be imported.
                        </div></div>';
                echo '<div class="field"><div class="orsee-table orsee-table-tablet-2cols orsee-table-mobile">';
                echo '<div class="orsee-table-row orsee-table-head"><div class="orsee-table-cell">Profile field</div><div class="orsee-table-cell">Column type</div>
                                <div class="orsee-table-cell">Form field type</div><div class="orsee-table-cell">Import from '.$old_database.'</div></div>';
                foreach ($new_fields as $field=>$f) {
                    echo '<div class="orsee-table-row"><div class="orsee-table-cell" data-label="Profile field">'.$field.'</div><div class="orsee-table-cell" data-label="Column type">'.$f['Type'].'</div><div class="orsee-table-cell" data-label="Form field type">';
                    if (isset($f['fieldtype'])) {
                        echo $f['fieldtype'];
                    } else {
                        echo '???';
                    }
                    echo '</div><div class="orsee-table-cell" data-label="Import from '.$old_database.'">';
                    echo '<span class="select is-primary"><SELECT name="map_'.$field.'">';
                    echo '<OPTION value="">Don\'t import</OPTION>';
                    foreach ($old_fields as $of) {
                        echo '<OPTION value="'.$of.'"';
                        if ($of==$field) {
                            echo ' SELECTED';
                        }
                        echo '>'.$of.'</OPTION>';
                    }
                    echo '</SELECT></span>';
                    echo '</div></div>';
                }
                echo '</div></div>';

                echo '<div class="field"><hr class="orsee-option-divider"></div>';

                echo '<div class="field"><div class="control">Do you want to create new (more secure) tokens for participant URLs?
                            <UL>
                            <LI>The disadvantage of this is that the old URL\'s which participants used to access their
                            ORSEE enrolment page won\'t work anymore.<BR>The new URLs (with the newly created tokens)
                            will be included in any email sent out by the system. </LI>
                            <LI>If you plan to migrate to a username/password authentication for participants in the new system,<BR>
                            you could also leave the tokens as they are, because once fully migrated they will be irrelevant for access.</LI>
                            </UL>
                            </div></div>';
                echo '<div class="field has-text-centered"><div class="control"><span class="select is-primary"><SELECT name="replace_tokens">
                            <OPTION value="n">Keep old tokens</OPTION>
                            <OPTION value="y">Replace tokens</OPTION>
                            </SELECT></span></div></div>';

                echo '<div class="field orsee-form-actions has-text-centered">
                        <INPUT type="submit" class="button orsee-btn" name="submit2" value="Next">
                    </div>';
                echo '</div></div>';
                echo '</FORM>';
            }

            if (isset($_REQUEST['submit2'])) {
                $code='';
                $code.='$old_db_name="'.$old_database.'";'."\n";
                $code.='$new_db_name="'.$site__database_database.'";'."\n";
                $code.=''."\n";
                $code.='// mapping of participant statuses'."\n";
                $code.='// participant_status_mapping[deleted y/n][excluded y/n]=status_id'."\n";
                $code.='$participant_status_mapping=array();'."\n";
                $code.='$participant_status_mapping["n"]["n"]="'.$_REQUEST['status_n_n'].'";'."\n";
                $code.='$participant_status_mapping["n"]["y"]="'.$_REQUEST['status_y_y'].'";'."\n";
                $code.='$participant_status_mapping["y"]["n"]="'.$_REQUEST['status_y_n'].'";'."\n";
                $code.='$participant_status_mapping["y"]["y"]="'.$_REQUEST['status_y_y'].'";'."\n";
                $code.=''."\n";
                $code.='// mapping of participation statuses'."\n";
                $code.='// participation_mapping[shownup y/n][participated y/n]=pstatus_id'."\n";
                $code.='$participation_mapping=array();'."\n";
                $code.='$participation_mapping["n"]["n"]="'.$_REQUEST['pstatus_n_n'].'";'."\n";
                $code.='$participation_mapping["n"]["y"]="'.$_REQUEST['pstatus_n_n'].'";'."\n";
                $code.='$participation_mapping["y"]["n"]="'.$_REQUEST['pstatus_y_n'].'";'."\n";
                $code.='$participation_mapping["y"]["y"]="'.$_REQUEST['pstatus_y_y'].'";'."\n";
                $code.=''."\n";
                $code.='// mapping from old participant profile form to new form'."\n";
                $code.='// empty value for new column name implies no import'."\n";
                $code.='// pform_mapping[old column name]=new column name'."\n";
                $code.='$pform_mapping=array();'."\n";
                $new_fields=participant__userdefined_columns();
                if (isset($_REQUEST['map_email'])) {
                    $new_fields['email']=array();
                }
                foreach ($new_fields as $field=>$f) {
                    if (isset($_REQUEST['map_'.$field]) && $_REQUEST['map_'.$field]) {
                        $code.='$pform_mapping["'.$_REQUEST['map_'.$field].'"]="'.$field.'";'."\n";
                    }
                }
                $code.=''."\n";
                $code.='// other settings'."\n";
                $code.='$import_type="'.$_REQUEST['import_type'].'";'."\n";
                $code.='$replace_tokens="'.$_REQUEST['replace_tokens'].'";'."\n";
                $code.=''."\n";
                $code.=''."\n";

                echo '<div class="orsee-panel"><div class="orsee-form-shell">';
                echo '<div class="field"><div class="control">Below you find the code to copy over to install/data_import.php.</div></div>';
                echo '<div class="field"><div class="control"><TEXTAREA class="textarea is-primary orsee-textarea" rows=40 cols=80>'.$code.'</TEXTAREA></div></div>';
                echo '</div></div>';
            }
        }
        /////////////////////////
    }
}
include("footer.php");

?>
