<?php

namespace matacms\language\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * I18nMessageSearch represents the model behind the search form about `matacms\language\models\I18nMessage`.
 */
class I18nMessageSearch extends I18nMessage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['language', 'translation', 'sourceMessage', 'category'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = I18nMessage::find()->with('sourceMessageModel')->joinWith('sourceMessageModel');

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }



        $query->andFilterWhere([
            '{{%matacms_i18n_source_message}}.id' => $this->id
        ]);

        $query->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.language', $this->language])
            ->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.translation', $this->translation])
            ->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.message', $this->sourceMessage])
            ->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.category', $this->category]);


        return $dataProvider;
    }
}
