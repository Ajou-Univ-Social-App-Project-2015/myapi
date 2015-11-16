<?php
require 'vendor/autoload.php';

include "generated-conf/config.php";

$app = new \Slim\Slim();

///////////////////////
// Routes >> Tests //
///////////////////////

$app->get('/tests/hello/:name', function ($name) {
  echo "Hello, " . $name;
});

///////////////////////
// Routes >> Authors //
///////////////////////

$app->get('/authors', function() use ($app) {
  $authors = \MyModel\AuthorQuery::create()->find();

  $response = array();
  foreach($authors as $author) {
    $response[] = array(
      "id"=>$author->getId(),
      "firstName"=>$author->getFirstName(),
      "lastName"=>$author->getLastName()
    );
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->get('/authors/:id', function($id) use ($app){
  $author = \MyModel\AuthorQuery::create()->findPK($id);

  if (null == $author) $app->notFound();

  $response = array(
    "id"=>$author->getId(),
    "firstName"=>$author->getFirstName(),
    "lastName"=>$author->getLastName()
  );

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->post('/authors', function() use ($app){
  $first_name = $app->request->post("first_name");
  $last_name = $app->request->post("last_name");

  $author = new \MyModel\Author();
  $author->setFirstName($first_name);
  $author->setLastName($last_name);
  $author->save();

  $app->response->setStatus(201);
  echo '';
});

$app->put('/authors/:id', function($id) use ($app){
  $author = \MyModel\AuthorQuery::create()->findPK($id);

  if (null == $author) $app->notFound();

  $first_name = $app->request->put("first_name");
  $last_name = $app->request->put("last_name");

  $payload = array();
  $payload["FirstName"] = (null == $first_name) ? $author->getFirstName() : $first_name;
  $payload["LastName"]= (null == $last_name) ? $author->getLastName() : $last_name;

  \MyModel\AuthorQuery::create()
    ->filterById($id)
    ->update($payload);

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

$app->delete('/authors/:id', function($id) use ($app){
  $author = \MyModel\AuthorQuery::create()->findPK($id);

  if (null == $author) $app->notFound();

  \MyModel\AuthorQuery::create()
    ->filterById($id)
    ->delete();

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

//////////////////////////
// Routes >> Publishers //
//////////////////////////

$app->get('/publishers', function() use ($app) {
  $publishers = \MyModel\PublisherQuery::create()->find();

  $response = array();
  foreach($publishers as $publisher) {
    $response[] = array(
      "id"=>$publisher->getId(),
      "name"=>$publisher->getName(),
    );
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->get('/publishers/:id', function($id) use ($app){
  $publisher = \MyModel\PublisherQuery::create()->findPK($id);

  if (null == $publisher) $app->notFound();

  $response = array(
    "id"=>$publisher->getId(),
    "name"=>$publisher->getName()
  );

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->post('/publishers', function() use ($app){
  $name = $app->request->post("name");

  $publisher = new \MyModel\Publisher();
  $publisher->setName($name);
  $publisher->save();

  $app->response->setStatus(201);
  echo '';
});

$app->put('/publishers/:id', function($id) use ($app){
  $publisher = \MyModel\PublisherQuery::create()->findPK($id);

  if (null == $publisher) $app->notFound();

  $name = $app->request->put("name");
  $payload = array();
  $payload["Name"] = (null == $name) ? $publisher->getName() : $name;

  \MyModel\PublisherQuery::create()->filterById($id)->update($payload);

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

$app->delete('/publishers/:id', function($id) use ($app) {
  $publisher = \MyModel\PublisherQuery::create()->findPK($id);

  if (null == $publisher) $app->notFound();

  \MyModel\PublisherQuery::create()->filterById($id)->delete();

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

/////////////////////
// Routes >> Books //
/////////////////////

$app->get('/books', function() use ($app) {
  $books = \MyModel\BookQuery::create()->find();

  $response = array();

  foreach($books as $book) {

    $book_json = array(
      "id"=>$book->getId(),
      "title"=>$book->getTitle(),
      "isbn"=>$book->getISBN()
    );

    if ($app->request->params('withPublisher')) {

      $publisher = \MyModel\PublisherQuery::create()->findPK($book->getPublisherId());

      $book_json["publisher"] = array(
        "id"=>$publisher->getId(),
        "name"=>$publisher->getName()
      );

    }

    if ($app->request->params('withAuthor')) {

      $author = \MyModel\AuthorQuery::create()->findPK($book->getAuthorId());

      $book_json["author"] = array(
        "id"=>$author->getId(),
        "firstName"=>$author->getFirstName(),
        "lastName"=>$author->getLastName()
      );

    }

    $response[] = $book_json;
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->get('/books/:id', function($id) use ($app) {
  $book = \MyModel\BookQuery::create()->findPK($id);

  if (null == $book) $app->notFound();

  $response = array(
    "id"=>$book->getId(),
    "title"=>$book->getTitle(),
    "isbn"=>$book->getISBN()
  );

  if ($app->request->params('withPublisher')) {
    $publisher = \MyModel\PublisherQuery::create()->findPK($book->getPublisherId());
    $response["publisher"] = array(
      "name"=>$publisher->getName()
    );
  }

  if ($app->request->params('withAuthor')) {
    $author = \MyModel\AuthorQuery::create()->findPK($book->getAuthorId());
    $response["author"] = array(
      "firstName"=>$author->getFirstName(),
      "lastName"=>$author->getLastName()
    );
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
});

$app->post('/books', function() use ($app){
  $title = $app->request->post("title");
  $isbn = $app->request->post("isbn");
  $publisher_id = $app->request->post("publisher_id");
  $author_id = $app->request->post("author_id");

  $book = new \MyModel\Book();
  $book->setTitle($title);
  $book->setISBN($isbn);
  $book->setPublisherId($publisher_id);
  $book->setAuthorId($author_id);
  $book->save();

  $app->response->setStatus(201); // succeeded and created new entity
  echo '';
});

$app->put('/books/:id', function($id) use ($app) {
  $book = \MyModel\BookQuery::create()->findPK($id);

  if (null == $book) $app->notFound();

  $title = $app->request->put("title");
  $isbn = $app->request->put("isbn");
  $publisher_id = $app->request->put("publisher_id");
  $author_id = $app->request->put("author_id");

  $payload = array();
  $payload["Title"] = (null == $title) ? $book->getTitle() : $title;
  $payload["ISBN"] = (null == $isbn) ? $book->getISBN() : $isbn;
  $payload["PublisherId"] = (null == $publisher_id) ? $book->getPublisherId() : $publisher_id;
  $payload["AuthorId"] = (null == $author_id) ? $book->getAuthorId() : $author_id;

  \MyModel\BookQuery::create()->filterById($id)->update($payload);

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

$app->delete('/books/:id', function($id) use ($app) {
  $book = \MyModel\BookQuery::create()->findPK($id);

  if (null == $book) $app->notFound();

  \MyModel\BookQuery::create()->filterById($id)->delete();

  $app->response->setStatus(204); // succeeded with no content
  echo '';
});

$app->run();
