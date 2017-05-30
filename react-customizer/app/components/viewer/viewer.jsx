import React from 'react'

export default class Viewer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      imgDir: './app/images/'
    }
  }

  render() {
    let hand = this.props.isRighty ? 'R' : 'L'
    let zoom = this.props.isZoomed ? 'zoomUrl' : 'imageUrl'
    let imageSourceWithConfiguration = zoom + `${hand}H`;

    let layeredImage = [];
    this.props.layers.forEach((layer, i) => {
      if(!layer) {return;}
      let src = layer[imageSourceWithConfiguration] === void 0 ? layer[zoom] : layer[imageSourceWithConfiguration]
      let imgUrl = this.state.imgDir + src;
      if (src === void 0){
        return 0;
      }
      layeredImage.push(
        <img style={{'zIndex': i}} key={i} src={imgUrl} className="stacked-image"/>
      )
    });

    return(
      <div className="previewer">
        {layeredImage}
      </div>
    )
  }
}
