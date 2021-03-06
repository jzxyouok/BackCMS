<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Addons;
use App\Http\Controllers\BackendController;
use App\Http\Requests\BackendRequest;

class ArchiveController extends BackendController
{
    public function getIndex()
    {
        $this->permission();

        $fieldIds = [ ];

        $archiveFields = \App\ArchiveField::all();

        foreach ( $archiveFields as $field ) {
            $fieldIds[] = $field->id;
        }

        \Plugins::register( 'pagination', 'App\Plugins\Pagination\Pagination' );

        $archives = \App\Archive::whereIn( 'archive_field_id', $fieldIds )->simplePaginate( 20 );

        return View( 'Backend.Archive.Index' )->with( [
            'archiveFields' => $archiveFields,
            'archives'      => $archives
        ] );
    }

    public function getCreate()
    {
        $this->permission();

        $templates = \App\Template::whereType( 2 )->get();

        return View( 'Backend.Archive.Create' )->with( [
            'templates' => $templates
        ] );
    }

    public function postCreate( BackendRequest $request )
    {
        $this->permission();

        $inputs             = $request->except( '_token' );
        $archiveNames       = $inputs[ 'archiveName' ];
        $attributeNames     = $inputs[ 'attributeName' ];
        $attributeTypes     = $inputs[ 'attributeType' ];
        $attributeLabels    = $inputs[ 'attributeLabel' ];
        $attributeDefaults  = $inputs[ 'attributeDefault' ];
        $attributeRequireds = isset( $inputs[ 'attributeRequired' ] ) ? $inputs[ 'attributeRequired' ] : 0;
        $attributeSelect    = isset( $inputs[ 'attributeSelect' ] ) ? $inputs[ 'attributeSelect' ] : null;
        $listTemplate       = $inputs[ 'list_template' ];
        $showTemplate       = $inputs[ 'show_template' ];

        $fields = [ ];

        foreach ( $attributeNames as $key => $name ) {
            if ( isset( $attributeRequireds[ $key ] ) ) {
                $required = $attributeRequireds[ $key ];
            } else {
                $required = '0';
            }

            $fields[] = [
                'name'     => $name,
                'type'     => $attributeTypes[ $key ],
                'label'    => $attributeLabels[ $key ],
                'default'  => $attributeDefaults[ $key ],
                'required' => $required,
            ];

            if ( $attributeTypes[ $key ] == 'select' ) {
                $fields[ $key ] = array_merge( $fields[ $key ], [
                    'options' => $attributeSelect[ $key ]
                ] );
            }
        }
        $archiveField                = new \App\ArchiveField;
        $archiveField->name          = $archiveNames;
        $archiveField->field         = json_encode( $fields );
        $archiveField->list_template = $listTemplate;
        $archiveField->show_template = $showTemplate;

        if ( $archiveField->save() )
            return Response()->json( [
                'code' => 'success',
            ] );
        else
            return Response()->json( [
                'code' => 'error',
            ] );

    }

    public function postAttributes( BackendRequest $request )
    {
        $this->permission();

        $field = $request->input( 'field' );

        return View( 'Backend.Archive.Template.Attributes' )->with( [
            'field' => $field
        ] );
    }

    public function getList( $id )
    {
        $this->permission();

        $field    = \App\ArchiveField::find( $id );
        $archives = \App\Archive::whereArchiveFieldId( $field->id )->get();

        return View( 'Backend.Archive.List' )->with( [
            'archives' => $archives,
            'field'    => $field
        ] );
    }

    public function getAdd( $id )
    {
        $this->permission();

        $field = \App\ArchiveField::find( $id );

        $attributes = json_decode( $field->field );

        \Plugins::register( 'Tags', 'App\Plugins\Tags\Tags' );

        return View( 'Backend.Archive.Add' )->with( [
            'field'      => $field,
            'attributes' => $attributes
        ] );
    }

    public function postAdd( BackendRequest $request, $id )
    {
        $this->permission();

        $inputs = $request->except( [
            '_token',
            'title',
            'keywords',
            'description'
        ] );

        $title       = $request->input( 'title' );
        $keywords    = $request->input( 'keywords' );
        $description = $request->input( 'description' );

        $archive = new \App\Archive;

        $archive->archive_field_id = $id;
        $archive->title            = $title;
        $archive->keywords         = $keywords;
        $archive->description      = $description;
        $archive->body             = json_encode( $inputs );

        if ( $archive->save() )
            return Response()->json( [
                'code' => 'success',
            ] );
        else
            return Response()->json( [
                'code' => 'error',
            ] );
    }

    public function getEdit( $id )
    {
        $this->permission();

        $archive = \App\Archive::find( $id );
        $field   = $archive->getArchiveField;

        \Plugins::register( 'Tags', 'App\Plugins\Tags\Tags' );

        return View( 'Backend.Archive.Edit' )->with( [
            'archive' => $archive,
            'field'   => $field
        ] );
    }

    public function postEdit( BackendRequest $request, $id )
    {
        $this->permission();

        $inputs = $request->except( [
            '_token',
            'title',
            'keywords',
            'description'
        ] );

        $title       = $request->input( 'title' );
        $keywords    = $request->input( 'keywords' );
        $description = $request->input( 'description' );

        $archive = \App\Archive::find( $id );

        $archive->archive_field_id = $id;
        $archive->title            = $title;
        $archive->keywords         = $keywords;
        $archive->description      = $description;
        $archive->body             = json_encode( $inputs );

        if ( $archive->save() )
            return Response()->json( [
                'code' => 'success',
            ] );
        else
            return Response()->json( [
                'code' => 'error',
            ] );
    }

    public function postDelete( BackendRequest $request )
    {
        $this->permission();

        $ids = $request->input( 'ids' );

        if ( is_array( $ids ) ) {
            $return = [ ];
            foreach ( $ids as $id ) {
                $archive  = \App\Archive::find( $id );
                $return[] = $archive->delete();
            }

            if ( $return ) {
                return Response()->json( [
                    'code' => 'success',
                ] );
            } else {
                return Response()->json( [
                    'code' => 'error',
                ] );
            }
        } else {
            $archive = \App\Archive::find( $ids );

            if ( $archive->delete() )
                return Response()->json( [
                    'code' => 'success',
                ] );
            else
                return Response()->json( [
                    'code' => 'error',
                ] );
        }
    }

    public function postDeleteField( BackendRequest $request )
    {
        $this->permission();

        $id           = $request->input( 'id' );
        $archiveField = \App\ArchiveField::find( $id );

        if ( $archiveField->delete() )
            return Response()->json( [
                'code' => 'success',
            ] );
        else
            return Response()->json( [
                'code' => 'error',
            ] );
    }
}
