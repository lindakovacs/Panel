<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Pterodactyl\Repositories\ServiceRepository;

use DB;
use Validator;
use Pterodactyl\Models;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Exceptions\DisplayValidationException;

class Variable
{
    public function __construct()
    {
        //
    }

    public function create($id, array $data)
    {
        $option = Models\ServiceOption::select('id')->findOrFail($id);

        $validator = Validator::make($data, [
            'name' => 'required|string|min:1|max:255',
            'description' => 'required|string',
            'env_variable' => 'required|regex:/^[\w]{1,255}$/',
            'default_value' => 'string|max:255',
            'user_viewable' => 'sometimes|required|nullable|boolean',
            'user_editable' => 'sometimes|required|nullable|boolean',
            'required' => 'sometimes|required|nullable|boolean',
            'regex' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            throw new DisplayValidationException($validator->errors());
        }

        if ($data['default_value'] !== '' && ! preg_match($data['regex'], $data['default_value'])) {
            throw new DisplayException('The default value you entered cannot violate the regex requirements.');
        }

        if (Models\ServiceVariable::where('env_variable', $data['env_variable'])->where('option_id', $option->id)->first()) {
            throw new DisplayException('An environment variable with that name already exists for this option.');
        }

        $data['user_viewable'] = (isset($data['user_viewable']) && in_array((int) $data['user_viewable'], [0, 1])) ? $data['user_viewable'] : 0;
        $data['user_editable'] = (isset($data['user_editable']) && in_array((int) $data['user_editable'], [0, 1])) ? $data['user_editable'] : 0;
        $data['required'] = (isset($data['required']) && in_array((int) $data['required'], [0, 1])) ? $data['required'] : 0;
        $data['option_id'] = $option->id;

        $variable = Models\ServiceVariable::create($data);

        return $variable;
    }

    public function delete($id)
    {
        $variable = Models\ServiceVariable::with('serverVariable')->findOrFail($id);

        DB::beginTransaction();
        try {
            foreach ($variable->serverVariable as $svar) {
                $svar->delete();
            }
            $variable->delete();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public function update($id, array $data)
    {
        $variable = Models\ServiceVariable::findOrFail($id);

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|min:1|max:255',
            'description' => 'sometimes|required|string',
            'env_variable' => 'sometimes|required|regex:/^[\w]{1,255}$/',
            'default_value' => 'sometimes|string|max:255',
            'user_viewable' => 'sometimes|required|nullable|boolean',
            'user_editable' => 'sometimes|required|nullable|boolean',
            'required' => 'sometimes|required|nullable|boolean',
            'regex' => 'sometimes|required|string|min:1',
        ]);

        if ($validator->fails()) {
            throw new DisplayValidationException($validator->errors());
        }

        $data['default_value'] = (isset($data['default_value'])) ? $data['default_value'] : $variable->default_value;
        $data['regex'] = (isset($data['regex'])) ? $data['regex'] : $variable->regex;

        if ($data['default_value'] !== '' && ! preg_match($data['regex'], $data['default_value'])) {
            throw new DisplayException('The default value you entered cannot violate the regex requirements.');
        }

        if (Models\ServiceVariable::where('id', '!=', $variable->id)->where('env_variable', $data['env_variable'])->where('option_id', $variable->option_id)->first()) {
            throw new DisplayException('An environment variable with that name already exists for this option.');
        }

        $data['user_viewable'] = (isset($data['user_viewable']) && in_array((int) $data['user_viewable'], [0, 1])) ? $data['user_viewable'] : $variable->user_viewable;
        $data['user_editable'] = (isset($data['user_editable']) && in_array((int) $data['user_editable'], [0, 1])) ? $data['user_editable'] : $variable->user_editable;
        $data['required'] = (isset($data['required']) && in_array((int) $data['required'], [0, 1])) ? $data['required'] : $variable->required;

        // Not using $data because the function that passes into this function
        // can't do $requst->only() due to the page setup.
        $variable->fill([
            'name' => $data['name'],
            'description' => $data['description'],
            'env_variable' => $data['env_variable'],
            'default_value' => $data['default_value'],
            'user_viewable' => $data['user_viewable'],
            'user_editable' => $data['user_editable'],
            'required' => $data['required'],
            'regex' => $data['regex'],
        ]);

        return $variable->save();
    }
}
