import React from 'react';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';

export default class AdditionalView extends React.Component {

  render(){

    return(
      <div className="additional-view">
        <div className="zoomed">
          <img className="thumbnail" onClick={this.props.setZoom} src="http://placehold.it/75x75?text=zoom"/>
        </div>
        <div className="normal">
          <img className="thumbnail" onClick={this.props.setNormal} src="http://placehold.it/75x75?text=normal"/>
        </div>
      </div>
    )
  }
}
