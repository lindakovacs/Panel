{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- Permission is hereby granted, free of charge, to any person obtaining a copy --}}
{{-- of this software and associated documentation files (the "Software"), to deal --}}
{{-- in the Software without restriction, including without limitation the rights --}}
{{-- to use, copy, modify, merge, publish, distribute, sublicense, and/or sell --}}
{{-- copies of the Software, and to permit persons to whom the Software is --}}
{{-- furnished to do so, subject to the following conditions: --}}

{{-- The above copyright notice and this permission notice shall be included in all --}}
{{-- copies or substantial portions of the Software. --}}

{{-- THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR --}}
{{-- IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, --}}
{{-- FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE --}}
{{-- AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER --}}
{{-- LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, --}}
{{-- OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE --}}
{{-- SOFTWARE. --}}
@extends('layouts.admin')

@section('title')
    Add New Service Pack
@endsection

@section('content')
<div class="col-md-12">
    <ul class="breadcrumb">
        <li><a href="/admin">Admin Control</a></li>
        <li><a href="/admin/services">Services</a></li>
        <li class="active">New Service Pack</li>
    </ul>
    <h3 class="nopad">New Service Pack</h3><hr />
    <form action="{{ route('admin.services.packs.new') }}" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 form-group">
                <label class="control-label">Pack Name:</label>
                <div>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="My Awesome Pack" class="form-control" />
                    <p class="text-muted"><small>The name of the pack which will be seen in dropdown menus and to users.</small></p>
                </div>
            </div>
            <div class="col-md-6 form-group">
                <label class="control-label">Pack Version:</label>
                <div>
                    <input type="text" name="version" value="{{ old('version') }}" placeholder="v0.8.1" class="form-control" />
                    <p class="text-muted"><small>The version of the program included in this pack.</small></p>
                </div>
            </div>
            <div class="col-md-12 form-group">
                <label class="control-label">Description:</label>
                <div>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    <p class="text-muted"><small>Provide a description of the pack which will be shown to users.</small></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label class="control-label">Associated Service Option:</label>
                <select name="option" class="form-control">
                    @foreach($services as $service)
                        <option disabled>{{ $service->name }}</option>
                        @foreach($service->options as $option)
                            <option value="{{ $option->id }}" @if((int) request()->option === $option->id)selected="selected"@endif>&nbsp;&nbsp; -- {{ $option->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 fuelux">
                <label class="control-label">&nbsp;</label>
                <div>
                    <label class="checkbox-formheight checkbox-custom checkbox-inline highlight" data-initialize="checkbox">
                        <input class="sr-only" type="checkbox" name="selectable" value="1"> User Selectable
                    </label>
                </div>
            </div>
            <div class="col-md-3 fuelux">
                <label class="control-label">&nbsp;</label>
                <div>
                    <label class="checkbox-formheight checkbox-custom checkbox-inline highlight" data-initialize="checkbox">
                        <input class="sr-only" type="checkbox" name="visible" value="1"> Visible
                    </label>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <h5 class="nopad">File Upload</h5>
                <div class="well" style="margin-bottom:0">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label class="control-label">Package Archive:</label>
                            <input name="file_upload" type="file" accept=".tar.gz, application/gzip" />
                            <p class="text-muted"><small>This package file must be a <code>.tar.gz</code> archive of files to use for either building or running this pack.<br /><br />If your file is larger than <code>20MB</code> we recommend uploading it using SFTP. Once you have added this pack to the system, a path will be provided where you should upload the file.
                            This server is currently configured with the following limits: <code>upload_max_filesize={{ ini_get('upload_max_filesize') }}</code> and <code>post_max_size={{ ini_get('post_max_size') }}</code>. If your file is larger than either of those values this request will fail.</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    {!! csrf_field() !!}
                    <input type="submit" class="btn btn-sm btn-primary" value="Add Service Pack" />
            </div>
        </div>
    </form>
</div>
{!! Theme::js('js/vendor/ace/ace.js') !!}
{!! Theme::js('js/vendor/ace/ext-modelist.js') !!}
<script type="text/javascript">
$(document).ready(function () {
    $('#sidebar_links').find("a[href='/admin/services/packs']").addClass('active');
    const Editor = ace.edit('build_script');

    Editor.setTheme('ace/theme/chrome');
    Editor.getSession().setUseWrapMode(true);
    Editor.setShowPrintMargin(false);
    Editor.getSession().setMode('ace/mode/sh');
    Editor.setOptions({
        minLines: 12,
        maxLines: Infinity
    });

    Editor.setValue('{{ old('build_script') }}');
    Editor.on('change', event => {
        $('#editor_contents').val(Editor.getValue());
    });
});
</script>
@endsection
