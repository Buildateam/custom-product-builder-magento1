import React from  'react'
import MainAccordion from './accordion/main_accordion.jsx';

export default class ControlPanel extends React.Component {
  constructor(props){
    super(props);

    this.state = {
      activeGroup: this.props.data.groups[0],
    }

    this.setGroupAsActive = this.setGroupAsActive.bind(this);
  }

  setGroupAsActive(group) {
    this.setState({activeGroup: group})
  }


  render() {
    let data = this.props.data;

    return (
      <div className="control-panel">
        <MainAccordion
          group={this.state.activeGroup}
          accordionOptions={data.groups}
          setActiveOption={this.setGroupAsActive}
          onSelectOption={this.props.onSelectOption}
          selectedName={this.props.selectedName}
          selectedDescription={this.props.selectedDescription}
          selectedPrice={this.props.selectedPrice}
        />
      </div>
    )
  }
}
