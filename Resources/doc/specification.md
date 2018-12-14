Configurable RESTfull API Architeture 
========================================

#  Resource actions
A resource can have unlimited actions, such as create, read,  update, delete.

## Create
A client can use PUT , POST http method to create resource. when the data is received by a server,  it should transform the data to entity through any meanifull ways, such Symfony serializer,  JMS serializer, even Symfony form.

## Read(View, Index)
A client can use GET http method to get resource. it has two semantics, one is single read, another is colelction read. we call `single read` as `view`, `collection read` as `index`.

## View
For single read, it means the server returns a single resource, A client can use any unique fields combination of the entity to get the resource,  for example , `id` property of `User` entity, `code` property of  `Order` entity. developer can specify which fields are appropriate, no fields are mandatory.

## Index
For collection read, it is different, the server responds with a collection of resources, client can specify the page and the size of a collection, or the server can simply return all resources if this is is valid behavior.

## Update

A client can use POST, PUT http method to update resource.
the client can use any unique fields combination of the entity to get the resource, then the server as transform the data to entity as the `create` action does.

## Delete

A client can use `DELETE` http method to update resource.
the client can use any unique fields combination of the entity to get the resource.

# Resource operations

in our design, resource actions stands for a process of the data request, for example, A client tries to create a comment, the `create` action process will be triggered. it might have following data transform phrases.
```
validation -> deserialize(map data to entity) -> Persist(save the entity to database) -> normalize entity(serialize the created data to client)
```

for resource operations, we mean a general configurations for a request. we can specify its `action`(etc, create or update), route path , http methods， etc.
```
operations:
        create_comment:                 # operation name
            denormalization_context:    # denormalization
                groups: [write]
            factory:                    # factory to create resource
                method: createByPostId
                arguments: [$postId]
            action: create              # action types
            methods: ['POST']           # http method
            normalization_context:      # normalization
                groups: [read]
            path: /posts/{postId}/comments   # route path
            validation_groups: [videni_rest] # validation group
            defaults:                        # other http configurations
                _format: 'json'
```

## Paginator

Paginator is specific to `index` action, client can specify the filters, sortings, max result of a collection, also disable pagination or not.

### filters

### sortings

# Demo

the following is a full example of a resource RESTfull API configuration.

```yaml
App\Entity\Comment:
    denormalization_context:
        groups: ['write']
    normalization_context:
        groups: [read]
    route_prefix: /admin
    validation_groups: [videni_rest]
    factory: 'App\Factory\CommentFactory'
    repository:
        class: 'App\Repository\CommentRepository'
    operations:
        create_comment:
            denormalization_context:
                groups: [write]
            factory:
                method: createByPostId
                arguments: [$postId]
            action: create
            methods: ['POST']
            normalization_context:
                groups: [read]
            path: /posts/{postId}/comments
            validation_groups: [videni_rest]
            defaults:
                _format: 'json'
        get_comments:
            paginator: comment
            action: index
            repository:
                method: createQueryBuilderByPostId
                arguments: [$postId]
            methods: ['POST', 'GET']
            path: /posts/{postId}/comments
        delete_comment:
            action: delete
            path: /comments/{id}
        update_comment:
            denormalization_context:
                groups: [write]
            action: update
            normalization_context:
                groups: [read]
            validation_groups: [videni_rest]
        view_comment:
            action: view
            path: /comments/{id}
            repository:
                arguments: [$id]
            normalization_context:
                groups: [read]
    paginators:
        comment:
            max_results: 50
            sortings:
                publishedAt:
                    property_path: publishedAt
                    description: '按发布日期排序'
                    order: asc
                enabled: asc
            disable_sorting: false
            filters:
                publishedAt:
                    type: string
                    description: '发布日期'
                    property_path: 'publishedAt'
                    allow_array: false
                    allow_range: true
                    options: ~
                    operators: ['<', '<=', '>', '>=']
                    collection: false
                enabled:
                    type: boolean
                    description: '是否启用'
                    allow_array: false
                    allow_range: true
                    options: ~
                    operators: ['=', '!=', '<', '<=', '>']
                    collection: false
```
