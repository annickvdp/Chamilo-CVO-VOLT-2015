<?php
/* For licensing terms, see /license.txt */

/**
 * Class CoursesController
 *
 * This file contains class used like controller, it should be included inside a dispatcher file (e.g: index.php)
 * @author Christian Fasanando <christian1827@gmail.com> - BeezNest
 * @package chamilo.auth
 */
class CoursesController
{
    private $toolname;
    private $view;
    private $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->toolname = 'auth';
        $actived_theme_path = api_get_template();
        $this->view = new View($this->toolname, $actived_theme_path);
        $this->model = new Auth();
    }

    /**
     * It's used for listing courses,
     * render to courses_list view
     * @param string   	action
     * @param string    confirmation message(optional)
     */
    public function courses_list($action, $message = '')
    {
        $data = array();
        $user_id = api_get_user_id();

        $data['user_courses']             = $this->model->get_courses_of_user($user_id);
        $data['user_course_categories']   = $this->model->get_user_course_categories();
        $data['courses_in_category']      = $this->model->get_courses_in_category();
        $data['all_user_categories']      = $this->model->get_user_course_categories();
        $data['action'] = $action;
        $data['message'] = $message;

        // render to the view
        $this->view->set_data($data);
        $this->view->set_layout('layout');
        $this->view->set_template('courses_list');
        $this->view->render();
    }

    /**
     * It's used for listing categories,
     * render to categories_list view
     * @param string   	$action
     * @param string   $message confirmation message(optional)
     * @param string   $error error message(optional)
     */
    public function categories_list($action, $message='', $error='')
    {
        $data = array();
        $data['user_course_categories'] = $this->model->get_user_course_categories();
        $data['action'] = $action;
        $data['message'] = $message;
        $data['error'] = $error;

        // render to the view
        $this->view->set_data($data);
        $this->view->set_layout('layout');
        $this->view->set_template('categories_list');
        $this->view->render();
    }

    /**
     * It's used for listing courses with categories,
     * render to courses_categories view
     * @param $action
     * @param string $category_code
     * @param string $message
     * @param string $error
     * @param string $content
     * @param array $limit will be used if $random_value is not set.
     * This array should contains 'start' and 'length' keys
     * @internal param \action $string
     * @internal param \Category $string code (optional)
     */
    public function courses_categories($action, $category_code = null, $message = '', $error = '', $content = null, $limit = array())
    {
        $data = array();
        $browse_course_categories = $this->model->browse_course_categories();

        global $_configuration;

        $data['countCoursesInCategory'] = $this->model->count_courses_in_category($category_code);
        if ($action == 'display_random_courses') {
            // Random value is used instead limit filter
            $data['browse_courses_in_category'] = $this->model->browse_courses_in_category(null, 10);
            $data['countCoursesInCategory'] = count($data['browse_courses_in_category']);
        } else {
            if (!isset($category_code)) {
                $category_code = $browse_course_categories[0][1]['code']; // by default first category
            }
            $limit = isset($limit) ? $limit : getLimitArray();
            $data['browse_courses_in_category'] = $this->model->browse_courses_in_category($category_code, null, $limit);
        }

        $data['browse_course_categories'] = $browse_course_categories;
        $data['code'] = Security::remove_XSS($category_code);

        // getting all the courses to which the user is subscribed to
        $curr_user_id = api_get_user_id();
        $user_courses = $this->model->get_courses_of_user($curr_user_id);
        $user_coursecodes = array();

        // we need only the course codes as these will be used to match against the courses of the category
        if ($user_courses != '') {
            foreach($user_courses as $key => $value) {
                $user_coursecodes[] = $value['code'];
            }
        }

        if (api_is_drh()) {
            $courses = CourseManager::get_courses_followed_by_drh(api_get_user_id());
            foreach ($courses as $course) {
                $user_coursecodes[] = $course['code'];
            }
        }

        $data['user_coursecodes'] = $user_coursecodes;
        $data['action']           = $action;
        $data['message']          = $message;
        $data['content']          = $content;
        $data['error']            = $error;

        $data['catalogShowCoursesSessions'] = 0;

        if (isset($_configuration['catalog_show_courses_sessions'])) {
            $data['catalogShowCoursesSessions'] = $_configuration['catalog_show_courses_sessions'];
        }

        // render to the view
        $this->view->set_data($data);
        $this->view->set_layout('layout');
        $this->view->set_template('courses_categories');
        $this->view->render();
    }

    /**
     * @param string $search_term
     * @param string $message
     * @param string $error
     * @param string $content
     * @param $limit
     */
    public function search_courses($search_term, $message = '', $error = '', $content = null, $limit = array())
    {
        $data = array();
        $limit = !empty($limit) ? $limit : getLimitArray();

        $browse_course_categories = $this->model->browse_course_categories();
        $data['countCoursesInCategory'] = $this->model->count_courses_in_category('ALL', $search_term);
        $data['browse_courses_in_category'] = $this->model->search_courses($search_term, $limit);
        $data['browse_course_categories']   = $browse_course_categories;

        $data['search_term'] = Security::remove_XSS($search_term); //filter before showing in template

        // getting all the courses to which the user is subscribed to
        $curr_user_id = api_get_user_id();
        $user_courses = $this->model->get_courses_of_user($curr_user_id);
        $user_coursecodes = array();

        // we need only the course codes as these will be used to match against the courses of the category
        if ($user_courses != '') {
            foreach ($user_courses as $value) {
                $user_coursecodes[] = $value['code'];
            }
        }

        $data['user_coursecodes'] = $user_coursecodes;
        $data['message'] = $message;
        $data['content'] = $content;
        $data['error'] = $error;
        $data['action'] = 'display_courses';

        // render to the view
        $this->view->set_data($data);
        $this->view->set_layout('layout');
        $this->view->set_template('courses_categories');
        $this->view->render();
    }

    /**
     * Auto user subscription to a course
     */
    public function subscribe_user($course_code, $search_term, $category_code)
    {
        $courseInfo = api_get_course_info($course_code);
        // The course must be open in order to access the auto subscription
        if (in_array($courseInfo['visibility'], array(COURSE_VISIBILITY_CLOSED, COURSE_VISIBILITY_REGISTERED, COURSE_VISIBILITY_HIDDEN))) {
            $error = get_lang('SubscribingNotAllowed');
            //$message = get_lang('SubscribingNotAllowed');
        } else {
            $result = $this->model->subscribe_user($course_code);
            if (!$result) {
                $error = get_lang('CourseRegistrationCodeIncorrect');
            } else {
                // Redirect directly to the course after subscription
                $message = $result['message'];
                $content = $result['content'];
            }
        }

        if (!empty($search_term)) {
            $this->search_courses($search_term, $message, $error, $content);
        } else {
            $this->courses_categories('subscribe', $category_code, $message, $error, $content);
        }
        return $result;
    }

    /**
     * Create a category
     * render to listing view
     * @param   string  Category title
     */
    public function add_course_category($category_title)
    {
        $result = $this->model->store_course_category($category_title);
        $message = '';
        if ($result) {
            $message = get_lang("CourseCategoryStored");
        } else {
            $error = get_lang('ACourseCategoryWithThisNameAlreadyExists');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Change course category
     * render to listing view
     * @param string    Course code
     * @param int    Category id
     */
    public function change_course_category($course_code, $category_id)
    {
        $result = $this->model->store_changecoursecategory($course_code, $category_id);
        $message = '';
        if ($result) {
            $message = get_lang('EditCourseCategorySucces');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Move up/down courses inside a category
     * render to listing view
     * @param string    move to up or down
     * @param string    Course code
     * @param int    Category id
     */
    public function move_course($move, $course_code, $category_id)
    {
        $result = $this->model->move_course($move, $course_code, $category_id);
        $message = '';
        if ($result) {
            $message = get_lang('CourseSortingDone');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Move up/down categories
     * render to listing view
     * @param string    move to up or down
     * @param int    Category id
     */
    public function move_category($move, $category_id)
    {
        $result = $this->model->move_category($move, $category_id);
        $message = '';
        if ($result) {
            $message = get_lang('CategorySortingDone');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Edit course category
     * render to listing view
     * @param string Category title
     * @param int    Category id
     */
    public function edit_course_category($title, $category)
    {
        $result = $this->model->store_edit_course_category($title, $category);
        $message = '';
        if ($result) {
            $message = get_lang('CourseCategoryEditStored');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Delete a course category
     * render to listing view
     * @param int    Category id
     */
    public function delete_course_category($category_id)
    {
        $result = $this->model->delete_course_category($category_id);
        $message = '';
        if ($result) {
            $message = get_lang('CourseCategoryDeleted');
        }
        $action = 'sortmycourses';
        $this->courses_list($action, $message);
    }

    /**
     * Unsubscribe user from a course
     * render to listing view
     * @param string    Course code
     */
    public function unsubscribe_user_from_course($course_code, $search_term = null, $category_code = null)
    {
        $result = $this->model->remove_user_from_course($course_code);
        $message = '';
        if ($result) {
            $message = get_lang('YouAreNowUnsubscribed');
        }
        $action = 'sortmycourses';
        if (!empty($search_term)) {
            $this->search_courses($search_term, $message, $error);
        } else {
            $this->courses_categories('subcribe', $category_code, $message, $error);
        }
    }

    /**
     * Get the html block for courses categories
     * @param string $code Current category code
     * @param boolean $hiddenLinks Whether hidden links
     * @param array $limit
     * @return string The HTML block
     */
    public function getCoursesCategoriesBlock($code = null, $hiddenLinks = false, $limit = null)
    {
        $categories = $this->model->browse_course_categories();

        $html = '';

        if (!empty($categories)) {
            $action = 'display_courses';
            foreach ($categories[0] as $category) {
                $categoryName = $category['name'];
                $categoryCode = $category['code'];
                $categoryCourses = $category['count_courses'];

                $html .= '<li>';

                if ($code == $categoryCode) {
                    $html .= '<strong>';
                    $html .= "$categoryName ($categoryCourses)";
                    $html .= '</strong>';
                } else {
                    if (!empty($categoryCourses)) {
                        $html .= '<a href="' . getCourseCategoryUrl(
                                1,
                                $limit['length'],
                                $categoryCode,
                                $hiddenLinks,
                                $action
                            ) . '">';
                        $html .= "$categoryName ($categoryCourses)";
                        $html .= '</a>';
                    } else {
                        $html .= "$categoryName ($categoryCourses)";
                    }
                }

                if (!empty($categories[$categoryCode])) {
                    $html .= '<ul class="nav nav-list">';

                    foreach ($categories[$categoryCode] as $subCategory1) {
                        $subCategory1Name = $subCategory1['name'];
                        $subCategory1Code = $subCategory1['code'];
                        $subCategory1Courses = $subCategory1['count_courses'];

                        $html .= '<li>';

                        if ($code == $subCategory1Code) {
                            $html .= "<strong>$subCategory1Name ($subCategory1Courses)</strong>";
                        } else {
                            $html .= '<a href="' . getCourseCategoryUrl(
                                    1,
                                    $limit['length'],
                                    $categoryCode,
                                    $hiddenLinks,
                                    $action
                                ) . '">';
                            $html .= "$subCategory1Name ($subCategory1Courses)";
                            $html .= '</a>';
                        }

                        if (!empty($categories[$subCategory1Code])) {
                            $html .= '<ul class="nav nav-list">';

                            foreach ($categories[$subCategory1Code] as $subCategory2) {
                                $subCategory2Name = $subCategory2['name'];
                                $subCategory2Code = $subCategory2['code'];
                                $subCategory2Courses = $subCategory2['count_courses'];

                                $html .= '<li>';

                                if ($code == $subCategory2Code) {
                                    $html .= "<strong>$subCategory2Name ($subCategory2Courses)</strong>";
                                } else {
                                    $html .= '<a href="' . getCourseCategoryUrl(
                                            1,
                                            $limit['length'],
                                            $categoryCode,
                                            $hiddenLinks,
                                            $action
                                        ) . '">';
                                    $html .= "$subCategory2Name ($subCategory2Courses)";
                                    $html .= '</a>';
                                }

                                if (!empty($categories[$subCategory2Code])) {
                                    $html .= '<ul class="nav nav-list">';

                                    foreach ($categories[$subCategory2Code] as $subCategory3) {
                                        $subCategory3Name = $subCategory3['name'];
                                        $subCategory3Code = $subCategory3['code'];
                                        $subCategory3Courses = $subCategory3['count_courses'];

                                        $html .= '<li>';

                                        if ($code == $subCategory3Code) {
                                            $html .= "<strong>$subCategory3Name ($subCategory3Courses)</strong>";
                                        } else {
                                            $html .= '<a href="' . getCourseCategoryUrl(
                                                    1,
                                                    $limit['length'],
                                                    $categoryCode,
                                                    $hiddenLinks,
                                                    $action
                                                ) . '">';
                                            $html .= "$subCategory3Name ($subCategory3Courses)";
                                            $html .= '</a>';
                                        }

                                        $html .= '</li>';
                                    }

                                    $html .= '</ul>';
                                }

                                $html .= '</li>';
                            }

                            $html .= '</ul>';
                        }

                        $html .= '</li>';
                    }

                    $html .= '</ul>';
                }

                $html .= '</li>';
            }
        }

        return $html;
    }

    /**
     * Get a HTML button for subscribe to session
     * @param string $sessionName The session name
     * @return string The button
     */
    public function getRegisterInSessionButton($sessionName)
    {
        $sessionName = urlencode($sessionName);

        $url = api_get_path(WEB_PATH) . "main/inc/email_editor.php?action=subscribe_me_to_session&session=$sessionName";

        return Display::url(get_lang('Subscribe'), $url, array(
            'class' => 'btn btn-large btn-primary',
        ));
    }

    /**
     * Generate a label if the user has been  registered in session
     * @return string The label
     */
    public function getAlreadyRegisterInSessionLabel()
    {
        $icon = Display::return_icon('students.gif', get_lang('Student'));

        return Display::label($icon . ' ' . get_lang("AlreadyRegisteredToSession"), "info");
    }

    /**
     * Get a icon for a session
     * @param string $sessionName The session name
     * @return string The icon
     */
    public function getSessionIcon($sessionName)
    {
        return Display::return_icon('window_list.png', $sessionName, null, ICON_SIZE_BIG);
    }

    /**
     * Return Session Catalogue rendered view
     * @param string $action
     * @param string $nameTools
     * @param array $limit
     */
    public function sessionsList($action, $nameTools, $limit = array())
    {
        $date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
        $hiddenLinks = isset($_GET['hidden_links']) ? intval($_GET['hidden_links']) == 1 : false;

        $limit = isset($limit) ? $limit : getLimitArray();

        $countSessions = $this->model->countSessions($date);
        $sessions = $this->model->browseSessions($date, $limit);

        $pageTotal = intval(ceil(intval($countSessions) / $limit['length']));
        // Do NOT show pagination if only one page or less
        $cataloguePagination = $pageTotal > 1 ?
            getCataloguePagination($limit['current'], $limit['length'], $pageTotal) :
            '';
        $sessionsBlocks = array();

        // Get session list catalogue URL
        $sessionUrl = getCourseCategoryUrl(1, $limit['length'], null, 0, 'display_sessions');
        // Get session search catalogue URL
        $courseUrl = getCourseCategoryUrl(1, $limit['length'], null, 0, 'subscribe');

        foreach ($sessions as $session) {
            $sessionsBlocks[] = array(
                'id' => $session['id'],
                'name' => $session['name'],
                'nbr_courses' => $session['nbr_courses'],
                'nbr_users' => $session['nbr_users'],
                'coach_name' => $session['coach_name'],
                'is_subscribed' => $session['is_subscribed'],
                'icon' => $this->getSessionIcon($session['name']),
                'date' => SessionManager::getSessionFormattedDate($session),
                'subscribe_button' => $this->getRegisterInSessionButton($session['name'])
            );
        }

        $tpl = new Template();
        $tpl->assign('action', $action);
        $tpl->assign('showCourses', CoursesAndSessionsCatalog::showCourses());
        $tpl->assign('showSessions', CoursesAndSessionsCatalog::showSessions());
        $tpl->assign('api_get_self', api_get_self());
        $tpl->assign('sessionUrl', $sessionUrl);
        $tpl->assign('courseUrl', $courseUrl);
        $tpl->assign('nameTools', $nameTools);

        $tpl->assign('coursesCategoriesList', $this->getCoursesCategoriesBlock(null, false, $limit));
        $tpl->assign('cataloguePagination', $cataloguePagination);

        $tpl->assign('hiddenLinks', $hiddenLinks);
        $tpl->assign('searchToken', Security::get_token());

        $tpl->assign('searchDate', $date);
        $tpl->assign('web_session_courses_ajax_url', api_get_path(WEB_AJAX_PATH) . 'course.ajax.php');
        $tpl->assign('sessions_blocks', $sessionsBlocks);
        $tpl->assign('already_subscribed_label', $this->getAlreadyRegisterInSessionLabel());

        $contentTemplate = $tpl->get_template('auth/sessions_catalog.tpl');

        $tpl->display($contentTemplate);
    }

}
