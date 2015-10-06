<?php if(!class_exists('Settings_API_Util')) {

/**
 * Utility class to bring some sanity to WordPress's Settings API
 * @version 0.2 - custom w/introHTML
 * @author Dave Ross <dave@davidmichaelross.com>
 * @license BSD
 */
class Settings_API_Util {

  const ICON_THEMES = 'icon-themes';
  const ICON_COMMENTS = 'icon-edit-comments';
  const ICON_INDEX = 'icon-index';
  const ICON_LINKS = 'icon-link-manager';
  const ICON_UPLOAD = 'icon-upload';
  const ICON_PAGES = 'icon-edit-pages';
  const ICON_PLUGIN = 'icon-plugins';
  const ICON_TOOLS = 'icon-tools';
  const ICON_SETTINGS = 'icon-options-general';
  const ICON_POSTS = 'icon-edit';
  const ICON_USERS = 'icon-users';

  public $id;
  public $optionName;
  public $values;

  private $sections;
  public $introHTML;

  /**
   * Fetch/create a form object with the given ID
   * @staticvar SN_Tracker_Admin $instance Stores Singleton instances
   * @param string $id Form ID
   * @param string $optionName Name to store settings under in the WordPress wp_options table. Defaults to $id.
   * @return SN_Tracker_Admin instance
   */
  public static function forID($id, $optionName = null, $rest=null) {

    if(null === $optionName) {
      $optionName = $id;
    }

    static $instances;

    if(!isset($instances)) {
      $instances = array();
    }

    if(!isset($instances[$id])) {
      $instances[$id] = new Settings_API_Util($id, $optionName);
    }

    return $instances[$id];

  }

  private function __construct($id, $optionName) {

    $this->id = $id;
    $this->optionName = $optionName;
    $this->sections = array();
    $this->introHTML = '';
    $this->outroHTML = '';

    register_setting( $id, $optionName, array(__CLASS__, 'validate'));

    $this->values = get_option($optionName);

  }

  /**
   * Basic form validation/sanitization
   * @param array $input
   * @return array validated/filtered input
   */
  public static function validate(array $input) {
    $output = array();

    foreach($input as $key=>$value) {
      $output[$key] = trim($value);
    }

    return $output;

  }

  /**
   * Render a standard WordPress settings form
   * @param type $title Settings page title
   * @param type $icon Settings_API_Util::ICON_* constant
   * @todo Get HTML out of this PHP code
   */
  public function renderBasicForm($title, $icon) {

    if (MULTISITE && is_network_admin()) {
      $url = "edit.php?action=" . $this->id;
    } else {
      $url="options.php";
    }
    echo '<div class="wrap" id="' . $this->id . '">';
    echo '<div id="'.$icon.'" class="frmicon icon32"><br></div>';
    echo '<h2>'.$title.'</h2>';
    if(!empty($this->introHTML)) {
      echo '<div class="intro">' . $this->introHTML . '</div>';
    }
    echo '<form id="' . $this->id . '" action="' . $url . '" method="POST">';
    settings_fields($this->id);
    do_settings_sections($this->id);

    echo '<p class="submit">';
    echo '<input type="submit" name="submit" class="button-primary" value="'.__('Save').'" />';
    echo '</p>';

    echo '</form>';
    echo '<div class="outro">' . $this->outroHTML . '</div>';
    echo '</div>';

  }

  /**
   * Add a new setting to this form
   * @param type $id
   * @param type $title
   * @param type $sectionHeadCallback
   * @return Settings_API_Util_Section
   */
  public function addSection($id, $title, $sectionHeadCallback = null) {

    $section = new Settings_API_Util_Section($this, $id, $title, $sectionHeadCallback);
    $this->sections[$id] = $section;
    return $section;

  }

  /**
   * Retrieve an existing section with the given ID
   * @param string $id
   * @return Settings_API_Util_Section
   */
  public function getSection($id) {

    return $this->sections[$id];

  }
}

class Settings_API_Util_Section {

  public $settings;
  public $id;
  public $title;
  private $fields;

  function __construct($settings, $id, $title, $sectionHeadCallback = null) {

    if($sectionHeadCallback === null) {
      $sectionHeadCallback = array(__CLASS__, 'renderEmptySectionHead');
    }

    $this->settings = $settings;
    $this->id = $id;
    $this->title = $title;
    $this->fields = array();

    add_settings_section($id, $title, $sectionHeadCallback, $settings->id);

  }

  /**
   *
   * @param string $id
   * @param string $title
   * @param string $type Settings_API_Util_Field::TYPE_* constant
   * @param string $defaultValue
   * @return Settings_API_Util_Field
   */
  public function addField($id, $title, $type, $defaultValue = null, $additionalArgs = null) {

    $field = new Settings_API_Util_Field($this, $id, $title, $type, $defaultValue, $additionalArgs);

    $this->fields[$id] = $field;

    return $field;

  }

  /**
   * Retrieve an existing field with the given ID
   * @param string $id
   * @return Settings_API_Util_Field
   */
  public function getField($id) {

    return $this->fields[$id];

  }

  /**
   * Default callback for rendering the section heading
   */
  public static function renderEmptySectionHead() { echo ''; }
}

class Settings_API_Util_Field {

  const TYPE_TEXTFIELD = 'textfield';
  const TYPE_TEXTAREA = 'textarea';
  const TYPE_CHECKBOX = 'checkbox';
  const TYPE_RADIO = 'radio';
  const TYPE_SELECT = 'select';
  const TYPE_HIDDEN = 'hidden';

  public $section;

  function __construct($section, $id, $title, $type, $defaultValue, $additionalArgs) {

    $this->section = $section;
    $this->name = $id;
    $this->optionID = $section->id.'_'.$id;
    $this->defaultValue = $defaultValue;
    $this->type = $type;

    $values = array(
      'id'   =>$section->settings->optionName.'_'.$this->optionID,
      'name' => $section->settings->optionName.'['.$this->optionID.']',
    );

    if (isset($this->section->settings->values[$this->optionID])) {
      $values['value'] = $this->section->settings->values[$this->optionID];
    }
    else {
      $values['value'] = $defaultValue;
    }

    if ($additionalArgs) {
      $values = array_merge($values, $additionalArgs);
    }

    $this->values = $values;

    add_settings_field($section->id.'_'.$id, __( $title, 'wpclef' ), array(__CLASS__, $type), $section->settings->id, $section->id, $values);
  }

  function render($opts = array()) {
    call_user_func(array(__CLASS__, $this->type), array_merge($opts, $this->values));
  }

  ///////////////////
  // Field renderers
  ///////////////////

  /**
   * Textfield
   * @param array $options
   */
  public static function textfield(array $options) {
    if (!isset($options['placeholder'])) $options['placeholder'] = '';
    ob_start();
    do_action('settings_api_util_pre_textfield');
    echo "<input type=\"text\" name=\"{$options['name']}\" value=\"{$options['value']}\" id=\"{$options['name']}\" class=\"regular-text\" placeholder=\"{$options['placeholder']}\" />";
    do_action('settings_api_util_after_textfield');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_textfield', $html);
    echo $html;

  }

  /**
   * Textarea
   * @param array $options
   */
  public static function textarea(array $options) {
    ob_start();
    do_action('settings_api_util_pre_textarea');
    echo "<textarea name=\"{$options['name']}\">{$options['value']}</textarea>";
    do_action('settings_api_util_after_textarea');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_textarea', $html);
    echo $html;
  }

  /**
   * Checkbox
   * @param array $options
   */
  public static function checkbox(array $options) {
    ob_start();
    do_action('settings_api_util_pre_checkbox');
    if($options['value']) {
      echo "<input type=\"checkbox\" name=\"{$options['name']}\" value=\"1\" checked>";
    } else {
      echo "<input type=\"checkbox\" name=\"{$options['name']}\" value=\"1\">";
    }
    do_action('settings_api_util_after_checkbox');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_checkbox', $html);
    echo $html;
  }

  /**
   * Radio
   * @param array $options
   */
  public static function radio(array $options) {
    ob_start();
    do_action('settings_api_util_pre_radio');
    echo "<input type=\"radio\" name=\"{$options['name']}\" value=\"{$options['value']}\" />";
    do_action('settings_api_util_after_radio');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_radio', $html);
    echo $html;
  }

  /**
   * Select
   * @param array $options
   */
  public static function select(array $options) {
    ob_start();
    do_action('settings_api_util_pre_select');
    echo "<select name=\"{$options['name']}\">";
    foreach ($options['options'] as &$opt) {
      if (gettype($opt) == "array") {
        $display = $opt[0];
        $value = $opt[1];
      } else {
        $display = $value = $opt;
      }

      if ($value == $options['value']) {
        echo "<option selected";
      } else {
        echo "<option";
      }
      echo " value='". $value . "'>" . $display . "</option>";
    }
    echo "</select>";
    do_action('settings_api_util_after_select');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_select', $html);
    echo $html;
  }

  /**
  * Hidden
  * @param array $options
  */
  public static function hidden(array $options) {
    ob_start();
    do_action('settings_api_util_pre_hidden');
    echo "<input type=\"hidden\" name=\"{$options['name']}\" value=\"{$options['value']}\"/>";
    do_action('settings_api_util_after_hidden');
    $html = ob_get_contents();
    ob_end_clean();
    apply_filters('settings_api_util_hidden', $html);
    echo $html;
  }

}

}
?>
