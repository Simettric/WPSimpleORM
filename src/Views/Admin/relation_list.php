<div class="wrap">

    <h1><?php echo sprintf(__('%s relationShips with %s'), $relEntityName, get_the_title($post)) ?></h1>



    <?php if(false === in_array($relation_type, array(\Simettric\WPSimpleORM\AbstractEntity::MANY_TO_ONE, \Simettric\WPSimpleORM\AbstractEntity::ONE_TO_ONE))  || !count($items)){ ?>
    <form id="new_rel_form" action="" method="post">

        <h2><?php __("Add new relation") ?></h2>
        <table>
            <tr>
                <th>
                    <input id="new_rel" type="search"  autocomplete="off" value="" placeholder="search entity by title">
                    <input type="hidden" id="sim_orm_new_rel_id" name="sim_orm_new_rel_id" value="">
                </th>
                <td>
                    <input id="submit_rel" type="submit" value="Add related" class="button button-primary" />
                </td>
            </tr>

        </table>


    </form>
    <?php } ?>

    <table class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th scope="col" class="manage-column">
                <?php _e("Entity") ?>
            </th>
            <th scope="col" class="manage-column">
                <?php _e("Entity ID") ?>
            </th>
            <th scope="col" class="manage-column"></th>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php

        /**
         * @var $item \Simettric\WPSimpleORM\AbstractEntity
         */
        foreach($items as $item){ ?>
            <tr class="">
                <td class="title column-title has-row-actions column-primary page-title" data-colname="Title">
                    <strong>
                        <?php echo $item->getTitle() ?>
                    </strong>

                </td>
                <td><?php echo $item->getId() ?></td>
                <td style="text-align: right">
                    <a href="<?php echo $base_link ?>&sim_orm_remove_rel=<?php echo $item->getId() ?>" class="button">remove</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>

        <tfoot>
        <tr>
            <th scope="col" class="manage-column">
                <?php _e("Entity") ?>
            </th>
            <th scope="col" class="manage-column">
                <?php _e("Entity ID") ?>
            </th>
            <th scope="col" class="manage-column"></th>
        </tr>
        </tfoot>

    </table>

    <?php /*
    <h2><?php esc_html_e( 'Color Helper Classes' ); ?></h2>

    <div class="wp-pattern-example">
        <h3>Blocks</h3>

        <table class="wp-pattern-table">
            <thead>
            <tr>
                <th class="example-code"><?php esc_html_e( 'Class' ); ?></th>
                <th class="example-descrip"><?php esc_html_e( 'Description' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><code>.wp-ui-primary</code></td>
                <td><span class="wp-ui-primary"><?php esc_html_e( "Elements with this class uses the base color." ); ?></span></td>
            </tr>
            <tr>
                <td><code>.wp-ui-highlight</code></td>
                <td><span class="wp-ui-highlight"><?php esc_html_e( "Elements with this class uses the highlight color." ); ?></span></td>
            </tr>
            <tr>
                <td><code>.wp-ui-notification</code></td>
                <td><span class="wp-ui-notification"><?php esc_html_e( "Elements with this class uses the notification color." ); ?></span></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="wp-pattern-example">
        <h3>Text</h3>

        <table class="wp-pattern-table">
            <thead>
            <tr>
                <th class="wp-pattern-example-code"><?php esc_html_e( 'Class' ); ?></th>
                <th class="wp-pattern-example-descrip"><?php esc_html_e( 'Description' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><code>.wp-ui-text-primary</code></td>
                <td><span class="wp-ui-text-primary"><?php esc_html_e( "Text with this class uses the base color." ); ?></span></td>
            </tr>
            <tr>
                <td><code>.wp-ui-text-highlight</code></td>
                <td><span class="wp-ui-text-highlight"><?php esc_html_e( "Text with this class uses the highlight color." ); ?></span></td>
            </tr>
            <tr>
                <td><code>.wp-ui-text-notification</code></td>
                <td><span class="wp-ui-text-notification"><?php esc_html_e( "Text with this class uses the notification color." ); ?></span></td>
            </tr>
            <tr>
                <td><code>.wp-ui-text-icon</code></td>
                <td><span class="wp-ui-text-icon"><?php esc_html_e( "Text with this class uses the icon color." ); ?></span></td>
            </tr>
            </tbody>
        </table>
    </div>

    <h2><?php esc_html_e( 'Forms' ); ?></h2>

    <form>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="input-text">Text input</label>
                </th>
                <td>
                    <input type="text" name="input-text" placeholder="Text" /><br />
                    <pre>
&lt;input type="text" name="input-text" placeholder="Text" />
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-text">Select</label>
                </th>
                <td>
                    <select name="select">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select>
                    <pre>
&lt;select name="select">
  &lt;option>Option 1&lt;/option>
  &lt;option>Option 2&lt;/option>
  &lt;option>Option 3&lt;/option>
&lt;/select>
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="multi-select">Multiple Select</label>
                </th>
                <td>
                    <select name="multi-select" multiple="multiple">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                        <option>Option 4</option>
                        <option>Option 5</option>
                        <option>Option 6</option>
                    </select>
                    <pre>
&lt;select name="multi-select" multiple="multiple">
  &lt;option>Option 1&lt;/option>
  &lt;option>Option 2&lt;/option>
  &lt;option>Option 3&lt;/option>
  &lt;option>Option 4&lt;/option>
  &lt;option>Option 5&lt;/option>
  &lt;option>Option 6&lt;/option>
&lt;/select>
				</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="radio-buttons">Radio Buttons</label>
                </th>
                <td>
                    <input type="radio" name="radio-buttons" value="option-1"/> Option 1 <br />
                    <input type="radio" name="radio-buttons" value="option-2"/> Option 2 <br />
                    <input type="radio" name="radio-buttons" value="option-3"/> Option 3 <br />
                    <input type="radio" name="radio-buttons" value="option-4"/> Option 4 <br />
                    <pre>
&lt;input type="radio" name="radio-buttons" value="option-1" /> Option 1
&lt;input type="radio" name="radio-buttons" value="option-2" /> Option 2
&lt;input type="radio" name="radio-buttons" value="option-3" /> Option 3
&lt;input type="radio" name="radio-buttons" value="option-4" /> Option 4
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-checkbox">Checkbox</label>
                </th>
                <td>
                    <input type="checkbox" name="input-checkbox" /> Option 1<br />
                    <pre>
&lt;input type="checkbox" name="input-checkbox"/> Option 1
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="checkbox-array">Checkbox Array</label>
                </th>
                <td>
                    <input type='checkbox' name='checkbox-array[]' value='option-1'> Option 1<br />
                    <input type='checkbox' name='checkbox-array[]' value='option-2'> Option 2<br />
                    <input type='checkbox' name='checkbox-array[]' value='option-3'> Option 3<br />
                    <pre>
&lt;input type='checkbox' name='checkbox-array[]' value='option-1'> Option 1
&lt;input type='checkbox' name='checkbox-array[]' value='option-2'> Option 2
&lt;input type='checkbox' name='checkbox-array[]' value='option-3'> Option 3
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-fieldset">Fieldset and <br />HTML5 Elements</label>
                </th>
                <td>
                    <fieldset>
                        <legend>Legend</legend>
                        <input type="email" placeholder="Email" /> Email<br />
                        <input type="search" placeholder="Search" /> Search<br />
                        <input type="tel" placeholder="Telephone" /> Telephone<br />
                        <input type="text" placeholder="Text" /> Text<br />
                        <input type="url" placeholder="URL" /> URL<br />
                    </fieldset>
                    <pre>
&lt;fieldset>
  &lt;legend>Legend&lt;/legend>
  &lt;input type="email" placeholder="Email" /> Email
  &lt;input type="search" placeholder="Search" /> Search
  &lt;input type="tel" placeholder="Telephone" /> Telephone
  &lt;input type="text" placeholder="text" /> Text
  &lt;input type="url" placeholder="URL" /> URL
&lt;/fieldset>
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-time">Time Elements</label>
                </th>
                <td>
                    Date: <input name="input-date" type="date" /><br />
                    Month: <input name="input-month" type="month" /> <br />
                    Week: <input name="input-week" type="week" /><br />
                    Time: <input name="input-time" type="time" /><br />
                    Local Date and Time: <input name="input-datetime-local" type="datetime-local" />
                    <pre>
Date: &lt;input name="input-date" type="date" />
Month: &lt;input name="input-month" type="month" />
Week: &lt;input name="input-week" type="week" />
Time: &lt;input name="input-time" type="time" />
Local Date and Time: &lt;input name="input-datetime-local" type="datetime-local" />
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-time">Other Elements</label>
                </th>
                <td>
                    Number: <input name="input-number" type="number" min="0" max="20" /><br />
                    Range: <input name="input-range" type="range" /><br />
                    Color: <input name="input-color" type="color" /><br />
                    <pre>
Number: &lt;input name="input-number" type="number" min="0" max="20" />
Range: &lt;input name="input-range" type="range" />
Color: &lt;input name="input-color" type="color" />
						</pre>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="input-time">Buttons</label>
                </th>
                <td>
                    <input type="submit" value="Submit Input" class="button" /><br /><br />
                    <input type="button" value="Secondary Button" class="button-secondary" /><br /><br />
                    <input type="button" value="Primary Button" class="button-primary" />
                    <pre>
&lt;input type="submit" value="Submit Input" class="button" />
&lt;input type="button" value="Secondary Button" class="button-secondary" />
&lt;input type="button" value="Primary Button" class="button-primary" />
						</pre>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

 */ ?>

</div><!-- .wrap -->