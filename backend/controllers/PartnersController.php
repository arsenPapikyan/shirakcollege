<?php

namespace backend\controllers;

use Imagine\Image\Box;
use Yii;
use common\models\Partners;
use backend\models\PartnersControl;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * PartnersController implements the CRUD actions for Partners model.
 */
class PartnersController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Partners models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PartnersControl();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Partners model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Partners model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Partners();

        if ($model->load(Yii::$app->request->post()) ) {

            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }
            return $this->render('create', [
                'model' => $model,
            ]);

    }

    /**
     * Updates an existing Partners model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldImgName = $model->img_name;

        if ($model->load(Yii::$app->request->post())) {

            $imgFile = UploadedFile::getInstance($model, "img_name");

            if (!empty($imgFile)) {
                $imgPath = Yii::getAlias("@frontend") . "/web/images/partners/";


                $imgName = $imgFile->name = Yii::$app->security->generateRandomString() . '.' . $imgFile->extension;
                $imgFile->saveAs($imgPath . $imgName);

                /*deleted old img*/
                if (file_exists($imgPath . $oldImgName)) {
                    unlink($imgPath . $oldImgName);
                }

                /**
                 * compress image, and  change size image
                 */

                $path = $imgPath . $imgName;
                $image = Image::getImagine()->open($path);

                $width = $image->getSize()->getWidth() >= 200 ? 200 : $image->getSize()->getWidth();
                $height = $image->getSize()->getHeight() >= 150 ? 150 : $image->getSize()->getWidth();

                $image->thumbnail(new Box($width, $height))
                    ->save($path, ['jpeg_quality' => 70])
                    ->save($path, ['png_compression_level' => 9]);

                $model->img_name = $imgName;
            }

            if ($model->img_name == "") {
                $model->img_name = $oldImgName;
            }
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);

    }

    /**
     * Deletes an existing Partners model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Partners model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Partners the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Partners::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionUploadFile($id)
    {
        $model = $this->findModel($id);

        $imgFile = UploadedFile::getInstance($model, "img_name");
        $imgPath = Yii::getAlias("@frontend") . "/web/images/partners/";


        $imgName = $imgFile->name = Yii::$app->security->generateRandomString() . '.' . $imgFile->extension;
        $imgFile->saveAs($imgPath . $imgName);

        /*deleted old img*/
        if (file_exists($imgPath . $model->img_name)) {
            unlink($imgPath . $model->img_name);
        }

        /**
         * compress image, and  change size image
         */

        $path = $imgPath . $imgName;
        $image = Image::getImagine()->open($path);

        $width = $image->getSize()->getWidth() >= 200 ? 200 : $image->getSize()->getWidth();
        $height = $image->getSize()->getHeight() >= 150 ? 150 : $image->getSize()->getWidth();

        $image->thumbnail(new Box($width, $height))
            ->save($path, ['jpeg_quality' => 70])
            ->save($path, ['png_compression_level' => 9]);

        $model->img_name = $imgName;
        if ($model->save(false)) {
            return true;
        }
        return false;
    }

    public function actionDeleteFile($id)
    {
        $model = $this->findModel($id);
        if (!empty($model->img_name)) {

            if (file_exists(Yii::getAlias("@frontend") . "/web/images/partners/" . $model['img_name'])) {
                unlink(Yii::getAlias("@frontend") . "/web/images/partners/" . $model['img_name']);

            }
        }

        return true;
    }
}
