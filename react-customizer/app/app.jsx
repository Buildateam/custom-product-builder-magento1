import React from 'react';
import ReactDOM from 'react-dom';
import Master from './components/master.jsx';
import './styles/master.css';
import data from './model/guitar_data.js';

ReactDOM.render( <Master data={data} />, document.getElementById('customizer-container') );
