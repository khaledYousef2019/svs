<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\AdminSetting;
use App\Model\CustomPage;
use App\Repository\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LandingController extends Controller
{
    public $settingRepo;
    public function __construct()
    {
        $this->settingRepo = new SettingRepository();
    }

    // Custom page list
    public function adminCustomPageList(Request $request)
    {
        if ($request->ajax()) {
            $cp = CustomPage::select('id', 'title', 'key', 'description', 'status', 'created_at')->orderBy('data_order', 'ASC');
            return response()->json([
                'data' => $cp->get()
            ]);
        }
        return response()->json(['error' => 'Invalid request'], 400);
    }

    // Custom page add
    public function adminCustomPageAdd()
    {
        return response()->json([
            'title' => __("Add Page")
        ]);
    }

    // Edit the custom page
    public function adminCustomPageEdit($id)
    {
        $page = CustomPage::find($id);
        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        return response()->json([
            'title' => __("Update Page"),
            'page' => $page
        ]);
    }

    // Custom page save image
    public function adminCustomPageImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $response = uploadimage($request->file('file'), IMG_PATH);
            if ($response) {
                return response()->json([
                    'success' => true,
                    'image' => asset(IMG_PATH . $response),
                    'message' => "File updated successfully !!"
                ]);
            }
            return response()->json(['success' => false, 'message' => "File update failed !!"]);
        }
        return response()->json(['success' => false, 'message' => "File not found !!"]);
    }

    // Custom page save settings
    public function adminCustomPageSave(Request $request)
    {
        $rules = [
            'menu' => 'required|max:255',
            'title' => 'required'
        ];
        $messages = [
            'title.required' => __('Title Can\'t be empty!'),
            'menu.required' => __('Menu Can\'t be empty!'),
            'description.required' => __('Description Can\'t be empty!')
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['message' => $errors[0]], 422);
        }

        $custom_page = [
            'title' => $request->title,
            'key' => $request->menu,
            'description' => $request->description,
            'status' => STATUS_SUCCESS
        ];

        CustomPage::updateOrCreate(['id' => $request->edit_id], $custom_page);

        $message = $request->edit_id ? __('Custom page updated successfully') : __('Custom Page created successfully');

        return response()->json(['message' => $message]);
    }

    // Delete custom page
    public function adminCustomPageDelete($id)
    {
        if (isset($id)) {
            CustomPage::where('id', decrypt($id))->delete();
            return response()->json(['message' => __('Deleted Successfully')]);
        }

        return response()->json(['error' => 'Invalid ID'], 400);
    }

    // Change custom page order
    public function customPageOrder(Request $request)
    {
        $vals = explode(',', $request->vals);
        foreach ($vals as $key => $item) {
            CustomPage::where('id', $item)->update(['data_order' => $key]);
        }

        return response()->json(['message' => __('Page ordered change successfully')]);
    }

    // Landing Settings
    public function adminLandingSetting(Request $request)
    {
        $data['tab'] = $request->query('tab', 'hero');
        $data['adm_setting'] = allsetting();

        return response()->json($data);
    }

    // Save CMS settings
    public function adminLandingSettingSave(Request $request)
    {
        $rules = [];
        foreach ($request->all() as $key => $item) {
            if ($request->hasFile($key)) {
                $rules[$key] = 'image|mimes:jpg,jpeg,png|max:2000';
            }
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json(['message' => $errors[0]], 422);
        }

        foreach ($request->all() as $key => $item) {
            if (!empty($request->$key)) {
                $setting = AdminSetting::where('slug', $key)->first();
                if (empty($setting)) {
                    $setting = new AdminSetting();
                    $setting->slug = $key;
                }
                if ($request->hasFile($key)) {
                    $setting->value = uploadFile($request->$key, IMG_PATH, isset(allsetting()[$key]) ? allsetting()[$key] : '');
                } else {
                    $setting->value = $request->$key;
                }
                $setting->save();
            }
        }

        return response()->json(['message' => __('Setting Successfully Updated!')]);
    }
}
