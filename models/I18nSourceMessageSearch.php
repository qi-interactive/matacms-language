<?php

namespace matacms\language\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * I18nSourceMessageSearch represents the model behind the search form about `matacms\language\models\I18nSourceMessage`.
 */
class I18nSourceMessageSearch extends I18nSourceMessage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['category', 'message'], 'safe'],
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
        $query = I18nSourceMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            '{{%matacms_i18n_source_message}}.id' => $this->id,
        ]);

        $query->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.category', $this->category])
            ->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.message', $this->message]);

        return $dataProvider;
    }

    public function searchWithMessagesForLanguage($params, $language)
    {

        $query = I18nSourceMessage::find()->joinWith([
             'i18nMessages' => function ($query) use ($language) {
                 $query->onCondition(['language' => $language]);
             }
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        $query->andFilterWhere([
            '{{%matacms_i18n_source_message}}.id' => $this->id,
        ]);

        $query->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.category', $this->category])
            ->andFilterWhere(['like', '{{%matacms_i18n_source_message}}.message', $this->message]);

        return $dataProvider;
    }
}
